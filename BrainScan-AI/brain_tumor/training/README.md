# Training Folder

This folder contains scripts and results for training the Brain Tumor Detection model.

## Contents

- **train_model.py** - Main training script that:
  - Loads .mat dataset files from `../dataset/`
  - Preprocesses and splits data into train/val/test sets
  - Trains ResNet50 model with transfer learning
  - Evaluates performance on test set
  - Saves all results

- **training_history.json** - Training metrics (loss and accuracy) for each epoch
- **test_metrics.json** - Final test set evaluation metrics
- **training_config.json** - Hyperparameters used for training
- **training_curves.png** - Plot of loss and accuracy curves
- **trained_model.pt** - Trained PyTorch model weights

## How to Run Training

### Option 1: Using the batch script (Windows)
```bash
cd brain_tumor
run_training.bat
```

### Option 2: Direct Python execution
```bash
cd brain_tumor/training
python train_model.py
```

### Option 3: Using virtual environment
```bash
cd brain_tumor
.\venv310\Scripts\python.exe training\train_model.py
```

## Training Process

1. **Data Loading**: Loads all .mat files from the dataset folder (bt_set1-4)
2. **Preprocessing**: 
   - Resizes images to 512x512
   - Normalizes pixel values
   - Applies data augmentation (rotation, flip, contrast)
3. **Dataset Split**:
   - 70% training
   - 15% validation
   - 15% testing
4. **Model**: Fine-tuned ResNet50 (ImageNet pre-trained)
5. **Training**: 30 epochs with early stopping
6. **Output**: Model + metrics saved to this folder

## Expected Results

- High accuracy (>95% typical on brain tumor data)
- Saved model: `trained_model.pt`
- Test metrics in JSON format for analysis

## Customization

Edit `TRAINING_CONFIG` in `train_model.py` to adjust:
- `learning_rate`: Default 0.001
- `num_epochs`: Default 30
- `batch_size`: Default 32
- `test_files_per_set`: Set to >0 to test with limited files (quick test)

## Note

Training on CPU will be slow. GPU is recommended if available. The script automatically detects and uses CUDA if available.
