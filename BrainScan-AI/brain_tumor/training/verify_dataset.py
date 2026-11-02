#!/usr/bin/env python3
"""
Quick test to verify dataset and setup before full training
"""

import os
import sys
import scipy.io as sio
import numpy as np

def main():
    print("\n" + "="*60)
    print("BRAIN TUMOR DATASET VERIFICATION")
    print("="*60)
    
    # Get paths
    PROJECT_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    DATASET_PATH = os.path.join(PROJECT_ROOT, 'dataset')
    
    print(f"\nProject root: {PROJECT_ROOT}")
    print(f"Dataset path: {DATASET_PATH}")
    
    # Check if dataset folder exists
    if not os.path.exists(DATASET_PATH):
        print(f"\n✗ ERROR: Dataset folder not found at {DATASET_PATH}")
        return False
    
    print(f"✓ Dataset folder found")
    
    # Check each dataset
    bt_sets = ['bt_set1', 'bt_set2', 'bt_set3', 'bt_set4']
    total_files = 0
    
    for bt_set in bt_sets:
        set_path = os.path.join(DATASET_PATH, bt_set)
        
        if not os.path.exists(set_path):
            print(f"✗ {bt_set}: NOT FOUND")
            continue
        
        mat_files = sorted([f for f in os.listdir(set_path) if f.endswith('.mat')])
        print(f"✓ {bt_set}: {len(mat_files)} files")
        total_files += len(mat_files)
        
        # Try loading first file as sample
        if len(mat_files) > 0:
            try:
                sample_file = os.path.join(set_path, mat_files[0])
                mat = sio.loadmat(sample_file)
                
                if 'cjdata' in mat:
                    img = mat['cjdata']
                    print(f"    Sample: {mat_files[0]} -> shape {img.shape}, dtype {img.dtype}")
                else:
                    data_keys = [k for k in mat.keys() if not k.startswith('__')]
                    print(f"    Sample: {mat_files[0]} -> keys: {data_keys}")
            except Exception as e:
                print(f"    WARNING: Could not load sample file: {e}")
    
    print(f"\nTotal .mat files: {total_files}")
    
    if total_files == 0:
        print("\n✗ ERROR: No .mat files found in dataset!")
        print("Please ensure dataset folder contains bt_set1, bt_set2, bt_set3, bt_set4 with .mat files")
        return False
    
    print(f"\n✓ Dataset verification successful!")
    print(f"Ready to train with {total_files} images")
    
    # Check PyTorch
    print("\nChecking PyTorch setup...")
    try:
        import torch
        print(f"✓ PyTorch version: {torch.__version__}")
        print(f"✓ CUDA available: {torch.cuda.is_available()}")
        if torch.cuda.is_available():
            print(f"  Device: {torch.cuda.get_device_name(0)}")
    except ImportError:
        print("✗ PyTorch not installed!")
        return False
    
    print("\n" + "="*60)
    print("VERIFICATION COMPLETE - Ready to train!")
    print("="*60 + "\n")
    return True

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
