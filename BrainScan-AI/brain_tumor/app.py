#!/usr/bin/env python3
"""
Brain Tumor Detection Web App
Developed by Souvik Sadhukhan

To run this app:
1. Make sure you have Python 3.10+ installed
2. Install dependencies: pip install -r requirements.txt
3. Run: python app.py

If you encounter import errors, use the virtual environment:
.\venv\Scripts\python.exe app.py
"""

try:
    import flask
    import pickle
    from io import BytesIO
    import torch
    from torch import argmax, load, nn
    from torch.cuda import is_available
    from torch.nn import Sequential, Linear, SELU, Dropout, LogSigmoid, ReLU
    from PIL import Image
    import numpy as np
    import cv2
    import matplotlib
    matplotlib.use('Agg')
    import matplotlib.pyplot as plt
    from torchvision.transforms import Compose, ToTensor, Resize
    from torchvision.models import resnet50, ResNet50_Weights
    from flask import render_template, jsonify
    import os
    import base64
except ImportError as e:
    print(f"Import Error: {e}")
    print("\nTo fix this:")
    print("1. Install dependencies: pip install -r requirements.txt")
    print("2. Or use virtual environment: .\\venv\\Scripts\\python.exe app.py")
    exit(1)
UPLOAD_FOLDER = os.path.join('static', 'photos')
app = flask.Flask(__name__, template_folder='templates')
app.secret_key = "secret key"
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024

ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif'])

def allowed_file(filename):
	return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

LABELS = ['None', 'Meningioma', 'Glioma', 'Pitutary']

device = "cuda" if is_available() else "cpu"

resnet_model = resnet50(weights=ResNet50_Weights.IMAGENET1K_V1)

for param in resnet_model.parameters():
    param.requires_grad = True

n_inputs = resnet_model.fc.in_features
resnet_model.fc = Sequential(Linear(n_inputs, 1024),
                            nn.ReLU(),
                            Dropout(p=0.5),
                            Linear(1024, 512),
                            nn.ReLU(),
                            Dropout(p=0.5),
                            Linear(512, 4))

for name, child in resnet_model.named_children():
    for name2, params in child.named_parameters():
        params.requires_grad = True

resnet_model.to(device)

model_path = './models/bt_resnet50_model.pt'
if os.path.exists(model_path):
    resnet_model.load_state_dict(load(model_path, map_location=device))
    resnet_model.eval()
else:
    print(f"Warning: Model file not found at {model_path}. Using pretrained ResNet50 without fine-tuning.")
    resnet_model.eval()


# ============= GRAD-CAM IMPLEMENTATION =============
class GradCAM:
    """Generate Grad-CAM visualization for model predictions"""
    def __init__(self, model, target_layer):
        self.model = model
        self.target_layer = target_layer
        self.gradients = None
        self.activations = None
        self._register_hooks()
    
    def _register_hooks(self):
        def forward_hook(module, input, output):
            self.activations = output.detach()
        
        def backward_hook(module, grad_input, grad_output):
            self.gradients = grad_output[0].detach()
        
        self.target_layer.register_forward_hook(forward_hook)
        self.target_layer.register_backward_hook(backward_hook)
    
    def generate_cam(self, image_tensor, class_idx):
        """Generate Class Activation Map"""
        self.model.eval()
        image_tensor.requires_grad = True
        
        # Forward pass
        output = self.model(image_tensor)
        self.model.zero_grad()
        
        # Backward pass
        target_score = output[0, class_idx]
        target_score.backward()
        
        # Compute CAM
        gradients = self.gradients.mean(dim=[2, 3], keepdim=True)
        cam = (gradients * self.activations).sum(dim=1, keepdim=True)
        cam = torch.nn.functional.relu(cam)
        cam = torch.nn.functional.interpolate(cam, size=(512, 512), mode='bilinear', align_corners=False)
        cam = cam.squeeze().cpu().numpy()
        cam = (cam - cam.min()) / (cam.max() - cam.min() + 1e-8)
        
        return cam

grad_cam = None

def initialize_grad_cam():
    """Initialize Grad-CAM with the last convolutional layer"""
    global grad_cam
    grad_cam = GradCAM(resnet_model, resnet_model.layer4[-1])

# Initialize Grad-CAM after model is loaded
initialize_grad_cam()


def preprocess_image(image_bytes):
  transform = Compose([Resize((512, 512)), ToTensor()])
  img = Image.open(BytesIO(image_bytes)).convert('RGB')
  return transform(img).unsqueeze(0)

def get_prediction(image_bytes):
  tensor = preprocess_image(image_bytes=image_bytes)
  y_hat = resnet_model(tensor.to(device))
  class_id = argmax(y_hat.data, dim=1)
  return str(int(class_id)), LABELS[int(class_id)]

def get_prediction_with_confidence(image_bytes):
  """Get prediction with confidence scores"""
  tensor = preprocess_image(image_bytes=image_bytes)
  with torch.no_grad():
      y_hat = resnet_model(tensor.to(device))
  
  # Apply softmax to get probabilities
  probabilities = torch.nn.functional.softmax(y_hat, dim=1)[0]
  class_id = argmax(probabilities)
  
  confidence_dict = {}
  for idx, label in enumerate(LABELS):
      confidence_dict[label] = float(probabilities[idx])
  
  return str(int(class_id)), LABELS[int(class_id)], confidence_dict

def generate_heatmap(image_bytes, class_idx):
  """Generate Grad-CAM heatmap and return as base64"""
  tensor = preprocess_image(image_bytes=image_bytes)
  tensor = tensor.to(device)
  
  # Generate CAM
  cam = grad_cam.generate_cam(tensor, int(class_idx))
  
  # Read original image as RGB
  img = Image.open(BytesIO(image_bytes)).convert('RGB')
  img_np = np.array(img.resize((512, 512)))
  
  # Apply colormap
  heatmap = cv2.applyColorMap((cam * 255).astype(np.uint8), cv2.COLORMAP_JET)
  
  # Blend heatmap with image
  overlay = cv2.addWeighted(img_np if len(img_np.shape) == 3 else cv2.cvtColor(img_np, cv2.COLOR_GRAY2BGR), 0.6, heatmap, 0.4, 0)
  
  # Convert to base64
  _, buffer = cv2.imencode('.png', overlay)
  img_base64 = base64.b64encode(buffer).decode()
  
  return img_base64

@app.route('/', methods=['GET'])
def main():
    if flask.request.method == 'GET':
        return(flask.render_template('DiseaseDet.html'))     

@app.route("/uimg",methods=['GET','POST'])
def uimg():
    if flask.request.method == 'GET':
        return(flask.render_template('uimg.html'))     
    if flask.request.method == 'POST':
        file = flask.request.files['file']
        img_bytes = file.read()   
        class_id, class_name = get_prediction(img_bytes)
        return(flask.render_template('pred.html',result = class_name,file = file))

@app.route("/predict-with-heatmap", methods=['POST'])
def predict_with_heatmap():
    """Single image prediction with Grad-CAM heatmap"""
    if 'file' not in flask.request.files:
        return jsonify({'error': 'No file provided'}), 400
    
    file = flask.request.files['file']
    if file.filename == '':
        return jsonify({'error': 'No file selected'}), 400
    
    if not allowed_file(file.filename):
        return jsonify({'error': 'Invalid file format'}), 400
    
    img_bytes = file.read()
    class_id, class_name, confidence = get_prediction_with_confidence(img_bytes)
    heatmap_b64 = generate_heatmap(img_bytes, class_id)
    
    return jsonify({
        'prediction': class_name,
        'class_id': class_id,
        'confidence': confidence,
        'heatmap': f'data:image/png;base64,{heatmap_b64}',
        'filename': file.filename
    })

@app.route("/batch-upload", methods=['GET', 'POST'])
def batch_upload():
    """Handle batch upload of multiple MRI images"""
    if flask.request.method == 'GET':
        return flask.render_template('batch.html')
    
    if flask.request.method == 'POST':
        if 'files[]' not in flask.request.files:
            return jsonify({'error': 'No files provided'}), 400
        
        files = flask.request.files.getlist('files[]')
        
        if len(files) == 0:
            return jsonify({'error': 'No files selected'}), 400
        
        results = []
        errors = []
        
        for file in files:
            if file.filename == '':
                continue
            
            try:
                if not allowed_file(file.filename):
                    errors.append({
                        'filename': file.filename,
                        'error': 'Invalid file format'
                    })
                    continue
                
                img_bytes = file.read()
                class_id, class_name, confidence = get_prediction_with_confidence(img_bytes)
                heatmap_b64 = generate_heatmap(img_bytes, class_id)
                
                results.append({
                    'filename': file.filename,
                    'prediction': class_name,
                    'class_id': class_id,
                    'confidence': confidence,
                    'heatmap': f'data:image/png;base64,{heatmap_b64}'
                })
            except Exception as e:
                errors.append({
                    'filename': file.filename,
                    'error': str(e)
                })
        
        return flask.render_template('batch_results.html', 
                                     results=results, 
                                     errors=errors,
                                     total=len(files),
                                     success=len(results))
      
@app.errorhandler(500)
def server_error(error):
    return render_template('error.html'), 500

if __name__ == '__main__':
   	app.run(debug=True)