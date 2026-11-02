#!/usr/bin/env python3
"""
Brain Tumor Detection Model Training Script
Loads .mat dataset files, trains ResNet50 model, and saves results to training folder
"""

import torch
import torch.nn as nn
import torch.optim as optim
from torch.utils.data import Dataset, DataLoader
from sklearn.model_selection import train_test_split
from torchvision import transforms, models
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.metrics import confusion_matrix, classification_report, accuracy_score, precision_score, recall_score, f1_score
from PIL import Image
import os
import sys
import h5py
import json
import pickle
import warnings

warnings.filterwarnings('ignore')

# Optional tqdm for progress bars
try:
    from tqdm import tqdm
    HAS_TQDM = True
except ImportError:
    HAS_TQDM = False
    def tqdm(iterable, desc=None, leave=False):
        return iterable

# Set random seeds for reproducibility
torch.manual_seed(42)
np.random.seed(42)

# Training Configuration
TRAINING_CONFIG = {
    'learning_rate': 1e-4,
    'num_epochs': 80,
    'batch_size': 16,
    'weight_decay': 1e-4,
    'patience': 10,
    'image_size': 224,
    'num_classes': 4,
    'test_files_per_set': 0,
}

LABELS = ['None', 'Meningioma', 'Glioma', 'Pituitary']

# Set device
device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
print(f'Using device: {device}')

# Get paths
PROJECT_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATASET_PATH = os.path.join(PROJECT_ROOT, 'dataset')
TRAINING_OUTPUT_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(PROJECT_ROOT, 'models')

# Create directories if they don't exist
os.makedirs(TRAINING_OUTPUT_DIR, exist_ok=True)
os.makedirs(MODELS_DIR, exist_ok=True)

print(f"\n{'='*60}")
print("BRAIN TUMOR DETECTION - MODEL TRAINING")
print(f"{'='*60}")
print(f"Dataset Path: {DATASET_PATH}")
print(f"Training Output: {TRAINING_OUTPUT_DIR}")
print(f"Models Directory: {MODELS_DIR}")
print(f"Device: {device}")


class BrainTumorDataset(Dataset):
    """Custom Dataset for Brain Tumor Images"""
    def __init__(self, images, labels, transform=None):
        self.images = images
        self.labels = labels
        self.transform = transform
    
    def __len__(self):
        return len(self.images)
    
    def __getitem__(self, idx):
        image = self.images[idx]
        
        # Normalize image to 0-1 range
        if image.max() > 1:
            image = image / 255.0
        
        # Convert grayscale to RGB by duplicating channels
        if len(image.shape) == 2:
            image = np.stack([image] * 3, axis=0)  # Shape: (3, H, W)
        elif len(image.shape) == 3 and image.shape[0] == 1:
            image = np.repeat(image, 3, axis=0)  # Shape: (3, H, W)
        
        # Convert to PIL Image if needed
        if isinstance(image, np.ndarray):
            if image.shape[0] == 3:  # RGB format
                image = Image.fromarray((image.transpose(1, 2, 0) * 255).astype(np.uint8), mode='RGB')
            else:
                image = Image.fromarray((image * 255).astype(np.uint8), mode='L')
        
        if self.transform:
            image = self.transform(image)
        
        label = self.labels[idx]
        return image, label


def load_mat_images(dataset_path, bt_sets, test_files=0):
    """Load images from .mat files (MATLAB v7.3 format)"""
    images = []
    labels = []
    
    print(f"\nLoading dataset from {dataset_path}...")
    
    for idx, bt_set in enumerate(bt_sets):
        set_path = os.path.join(dataset_path, bt_set)
        
        if not os.path.exists(set_path):
            print(f"Warning: {set_path} not found")
            continue
        
        mat_files = sorted([f for f in os.listdir(set_path) if f.endswith('.mat')])
        
        # Limit files if test_files > 0
        if test_files > 0:
            mat_files = mat_files[:test_files]
        
        print(f"Loading {bt_set}... ({len(mat_files)} files)")
        print("  Progress: ", end="", flush=True)
        
        for i, mat_file in enumerate(mat_files):
            try:
                filepath = os.path.join(set_path, mat_file)
                with h5py.File(filepath, 'r') as f:
                    # Get image data from cjdata/image
                    if 'cjdata' in f and 'image' in f['cjdata']:
                        image = np.array(f['cjdata']['image'])
                        
                        # Ensure it's 2D
                        if len(image.shape) == 2 and image.size > 0:
                            # Convert to float32 and normalize to 0-1 range
                            image = image.astype(np.float32)
                            if image.max() > 1:
                                image = image / 255.0
                            
                            images.append(image)
                            labels.append(idx)
                    
            except Exception as e:
                pass
            
            # Print progress every 10% or at the end
            if (i + 1) % max(1, len(mat_files) // 10) == 0 or (i + 1) == len(mat_files):
                progress = ((i + 1) / len(mat_files)) * 100
                print(f"{progress:.0f}%...", end=" ", flush=True)
        
        print("✓")
        print(f"  Loaded {sum(1 for l in labels if l == idx)} images from {bt_set}")
    
    return images, labels


def train_epoch(model, train_loader, criterion, optimizer, device):
    """Train for one epoch"""
    model.train()
    total_loss = 0
    correct = 0
    total = 0
    batch_count = len(train_loader)
    
    print("  ", end="", flush=True)
    for batch_idx, (images, labels) in enumerate(train_loader):
        images, labels = images.to(device), labels.to(device)
        
        # Forward pass
        outputs = model(images)
        loss = criterion(outputs, labels)
        
        # Backward pass
        optimizer.zero_grad()
        loss.backward()
        optimizer.step()
        
        # Statistics
        total_loss += loss.item()
        _, predicted = torch.max(outputs.data, 1)
        total += labels.size(0)
        correct += (predicted == labels).sum().item()
        
        # Print progress
        progress = (batch_idx + 1) / batch_count * 100
        if (batch_idx + 1) % max(1, batch_count // 10) == 0:
            print(f"{progress:.0f}%...", end=" ", flush=True)
    
    print("✓")
    avg_loss = total_loss / len(train_loader)
    accuracy = 100 * correct / total
    return avg_loss, accuracy


def validate(model, val_loader, criterion, device):
    """Validate model"""
    model.eval()
    total_loss = 0
    correct = 0
    total = 0
    batch_count = len(val_loader)
    
    print("  ", end="", flush=True)
    with torch.no_grad():
        for batch_idx, (images, labels) in enumerate(val_loader):
            images, labels = images.to(device), labels.to(device)
            outputs = model(images)
            loss = criterion(outputs, labels)
            
            total_loss += loss.item()
            _, predicted = torch.max(outputs.data, 1)
            total += labels.size(0)
            correct += (predicted == labels).sum().item()
            
            # Print progress
            progress = (batch_idx + 1) / batch_count * 100
            if (batch_idx + 1) % max(1, batch_count // 10) == 0:
                print(f"{progress:.0f}%...", end=" ", flush=True)
    
    print("✓")
    avg_loss = total_loss / len(val_loader)
    accuracy = 100 * correct / total
    return avg_loss, accuracy


def evaluate_test_set(model, test_loader, device):
    """Evaluate on test set"""
    model.eval()
    all_preds = []
    all_labels = []
    batch_count = len(test_loader)
    
    print("  ", end="", flush=True)
    with torch.no_grad():
        for batch_idx, (images, labels) in enumerate(test_loader):
            images = images.to(device)
            outputs = model(images)
            _, predicted = torch.max(outputs.data, 1)
            
            all_preds.extend(predicted.cpu().numpy())
            all_labels.extend(labels.numpy())
            
            # Print progress
            progress = (batch_idx + 1) / batch_count * 100
            if (batch_idx + 1) % max(1, batch_count // 10) == 0:
                print(f"{progress:.0f}%...", end=" ", flush=True)
    
    print("✓")
    
    accuracy = accuracy_score(all_labels, all_preds)
    precision = precision_score(all_labels, all_preds, average='weighted', zero_division=0)
    recall = recall_score(all_labels, all_preds, average='weighted', zero_division=0)
    f1 = f1_score(all_labels, all_preds, average='weighted', zero_division=0)
    
    conf_matrix = confusion_matrix(all_labels, all_preds)
    class_report = classification_report(all_labels, all_preds, target_names=LABELS)
    
    return {
        'accuracy': accuracy,
        'precision': precision,
        'recall': recall,
        'f1': f1,
        'confusion_matrix': conf_matrix,
        'classification_report': class_report,
        'predictions': all_preds,
        'labels': all_labels
    }


def save_training_results(history, test_metrics, model, config):
    """Save training results to files"""
    
    # Save training history
    history_file = os.path.join(TRAINING_OUTPUT_DIR, 'training_history.json')
    history_data = {
        'train_loss': [float(x) for x in history['train_loss']],
        'train_acc': [float(x) for x in history['train_acc']],
        'val_loss': [float(x) for x in history['val_loss']],
        'val_acc': [float(x) for x in history['val_acc']],
    }
    with open(history_file, 'w') as f:
        json.dump(history_data, f, indent=4)
    print(f"✓ Training history saved to {history_file}")
    
    # Save test metrics
    metrics_file = os.path.join(TRAINING_OUTPUT_DIR, 'test_metrics.json')
    metrics_data = {
        'accuracy': float(test_metrics['accuracy']),
        'precision': float(test_metrics['precision']),
        'recall': float(test_metrics['recall']),
        'f1': float(test_metrics['f1']),
        'classification_report': test_metrics['classification_report'],
        'confusion_matrix': test_metrics['confusion_matrix'].tolist(),
    }
    with open(metrics_file, 'w') as f:
        json.dump(metrics_data, f, indent=4)
    print(f"✓ Test metrics saved to {metrics_file}")
    
    # Save configuration
    config_file = os.path.join(TRAINING_OUTPUT_DIR, 'training_config.json')
    with open(config_file, 'w') as f:
        json.dump(config, f, indent=4)
    print(f"✓ Training config saved to {config_file}")
    
    # Save model to training folder
    model_path = os.path.join(TRAINING_OUTPUT_DIR, 'trained_model.pt')
    torch.save(model.state_dict(), model_path)
    print(f"✓ Model saved to {model_path}")
    
    # Also save to models folder
    models_model_path = os.path.join(MODELS_DIR, 'bt_resnet50_model.pt')
    torch.save(model.state_dict(), models_model_path)
    print(f"✓ Model also saved to {models_model_path}")


def plot_training_curves(history, output_dir):
    """Plot and save training curves"""
    fig, (ax1, ax2) = plt.subplots(1, 2, figsize=(15, 5))
    
    # Loss plot
    ax1.plot(history['train_loss'], label='Train Loss', marker='o')
    ax1.plot(history['val_loss'], label='Val Loss', marker='o')
    ax1.set_xlabel('Epoch')
    ax1.set_ylabel('Loss')
    ax1.set_title('Training and Validation Loss')
    ax1.legend()
    ax1.grid(True)
    
    # Accuracy plot
    ax2.plot(history['train_acc'], label='Train Acc', marker='o')
    ax2.plot(history['val_acc'], label='Val Acc', marker='o')
    ax2.set_xlabel('Epoch')
    ax2.set_ylabel('Accuracy (%)')
    ax2.set_title('Training and Validation Accuracy')
    ax2.legend()
    ax2.grid(True)
    
    plt.tight_layout()
    plot_path = os.path.join(output_dir, 'training_curves.png')
    plt.savefig(plot_path, dpi=150)
    print(f"✓ Training curves saved to {plot_path}")
    plt.close()


def main():
    # Load dataset
    bt_sets = ['bt_set1', 'bt_set2', 'bt_set3', 'bt_set4']
    images, labels = load_mat_images(
        DATASET_PATH, 
        bt_sets, 
        test_files=TRAINING_CONFIG['test_files_per_set']
    )
    
    if len(images) == 0:
        print("ERROR: No images loaded. Please check dataset path.")
        return
    
    print(f"\nTotal images loaded: {len(images)}")
    print(f"Label distribution: {np.bincount(np.array(labels))}")
    
    # Convert to numpy arrays
    X = np.array(images, dtype=object)
    y = np.array(labels)
    
    # Split data
    print("\nSplitting dataset...")
    X_train, X_temp, y_train, y_temp = train_test_split(
        X, y, test_size=0.3, random_state=42, stratify=y
    )
    X_val, X_test, y_val, y_test = train_test_split(
        X_temp, y_temp, test_size=0.5, random_state=42, stratify=y_temp
    )
    
    print(f"  Train: {len(X_train)} | Val: {len(X_val)} | Test: {len(X_test)}")
    
    # Define transforms
    train_transform = transforms.Compose([
        transforms.Resize((TRAINING_CONFIG['image_size'], TRAINING_CONFIG['image_size'])),
        transforms.RandomHorizontalFlip(p=0.5),
        transforms.RandomRotation(10),
        transforms.ColorJitter(brightness=0.2, contrast=0.2),
        transforms.ToTensor(),
        transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225])  # ImageNet RGB normalization
    ])
    
    val_test_transform = transforms.Compose([
        transforms.Resize((TRAINING_CONFIG['image_size'], TRAINING_CONFIG['image_size'])),
        transforms.ToTensor(),
        transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225])
    ])
    
    # Create datasets and loaders
    train_dataset = BrainTumorDataset(X_train, y_train, transform=train_transform)
    val_dataset = BrainTumorDataset(X_val, y_val, transform=val_test_transform)
    test_dataset = BrainTumorDataset(X_test, y_test, transform=val_test_transform)
    
    train_loader = DataLoader(
        train_dataset, 
        batch_size=TRAINING_CONFIG['batch_size'], 
        shuffle=True
    )
    val_loader = DataLoader(
        val_dataset, 
        batch_size=TRAINING_CONFIG['batch_size'], 
        shuffle=False
    )
    test_loader = DataLoader(
        test_dataset, 
        batch_size=TRAINING_CONFIG['batch_size'], 
        shuffle=False
    )
    
    print(f"  DataLoaders created with batch size: {TRAINING_CONFIG['batch_size']}")
    
    # Load pretrained ResNet50
    print("\nLoading ResNet50 model...")
    model = models.resnet50(pretrained=True)
    
    # Freeze initial layers
    for param in model.parameters():
        param.requires_grad = False
    
    # Replace FC layer for softmax-friendly logits
    n_inputs = model.fc.in_features
    model.fc = nn.Sequential(
        nn.Linear(n_inputs, 1024),
        nn.ReLU(),
        nn.Dropout(p=0.5),
        nn.Linear(1024, 512),
        nn.ReLU(),
        nn.Dropout(p=0.5),
        nn.Linear(512, TRAINING_CONFIG['num_classes'])  # logits for CrossEntropy
    )
    
    # Unfreeze last layers for fine-tuning
    for name, child in model.named_children():
        if name in ['layer4', 'fc']:
            for params in child.parameters():
                params.requires_grad = True
    
    model.to(device)
    print("✓ Model loaded and configured")
    
    # Setup training
    criterion = nn.CrossEntropyLoss()
    optimizer = optim.Adam(
        filter(lambda p: p.requires_grad, model.parameters()),
        lr=TRAINING_CONFIG['learning_rate'],
        weight_decay=TRAINING_CONFIG['weight_decay']
    )
    scheduler = optim.lr_scheduler.ReduceLROnPlateau(
        optimizer, mode='min', factor=0.1, patience=3, min_lr=1e-6
    )
    
    # Training loop
    print(f"\n{'='*60}")
    print("STARTING TRAINING")
    print(f"{'='*60}")
    
    history = {'train_loss': [], 'train_acc': [], 'val_loss': [], 'val_acc': []}
    best_val_loss = float('inf')
    best_val_acc = 0.0
    epochs_no_improve = 0
    
    for epoch in range(TRAINING_CONFIG['num_epochs']):
        print(f"\nEpoch {epoch+1}/{TRAINING_CONFIG['num_epochs']}")
        print(f"{'-'*60}")
        
        # Train
        train_loss, train_acc = train_epoch(model, train_loader, criterion, optimizer, device)
        history['train_loss'].append(train_loss)
        history['train_acc'].append(train_acc)
        
        # Validate
        val_loss, val_acc = validate(model, val_loader, criterion, device)
        history['val_loss'].append(val_loss)
        history['val_acc'].append(val_acc)
        
        print(f"Train Loss: {train_loss:.4f} | Train Acc: {train_acc:.2f}%")
        print(f"Val Loss: {val_loss:.4f} | Val Acc: {val_acc:.2f}%")
        
        # Save best model based on validation accuracy
        if val_acc > best_val_acc:
            best_val_acc = val_acc
            epochs_no_improve = 0
            torch.save(model.state_dict(), os.path.join(MODELS_DIR, 'bt_resnet50_model.pt'))
            print("✓ New best model (val acc) - saving...")
        else:
            epochs_no_improve += 1

        # Early stopping
        if epochs_no_improve >= TRAINING_CONFIG['patience']:
            print(f"\nEarly stopping after {epoch+1} epochs (no improvement for {TRAINING_CONFIG['patience']} epochs)")
            break

        # Learning rate scheduler
        scheduler.step(val_loss)

        # Optional: increase difficulty by unfreezing progressively
        if epoch == 5:
            print("\nUnfreezing layer3 and layer4 for fine-tuning after warm-up")
            for name, child in model.named_children():
                if name in ['layer3', 'layer4', 'fc']:
                    for params in child.parameters():
                        params.requires_grad = True
    
    # Evaluate on test set
    print(f"\n{'='*60}")
    print("EVALUATING ON TEST SET")
    print(f"{'='*60}")
    
    test_metrics = evaluate_test_set(model, test_loader, device)
    
    print(f"\nTest Metrics:")
    print(f"  Accuracy:  {test_metrics['accuracy']:.4f}")
    print(f"  Precision: {test_metrics['precision']:.4f}")
    print(f"  Recall:    {test_metrics['recall']:.4f}")
    print(f"  F1-Score:  {test_metrics['f1']:.4f}")
    
    print(f"\nClassification Report:\n{test_metrics['classification_report']}")
    
    # Save results
    print(f"\n{'='*60}")
    print("SAVING RESULTS")
    print(f"{'='*60}")
    
    save_training_results(history, test_metrics, model, TRAINING_CONFIG)
    plot_training_curves(history, TRAINING_OUTPUT_DIR)
    
    print(f"\n{'='*60}")
    print("TRAINING COMPLETE")
    print(f"{'='*60}")
    print(f"\nResults saved to: {TRAINING_OUTPUT_DIR}")


if __name__ == "__main__":
    main()
