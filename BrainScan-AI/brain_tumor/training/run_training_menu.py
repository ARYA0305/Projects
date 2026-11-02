#!/usr/bin/env python3
"""
Brain Tumor Detection Training Runner
Interactive script with menu options for training and verification
"""

import os
import sys
import subprocess
from pathlib import Path

def get_project_paths():
    """Get project paths"""
    script_dir = Path(__file__).parent.parent  # brain_tumor folder
    return {
        'project': script_dir,
        'training': script_dir / 'training',
        'dataset': script_dir / 'dataset',
        'models': script_dir / 'models'
    }

def verify_dataset():
    """Run dataset verification"""
    paths = get_project_paths()
    verify_script = paths['training'] / 'verify_dataset.py'
    
    print("\n" + "="*60)
    print("Running dataset verification...")
    print("="*60 + "\n")
    
    try:
        subprocess.run([sys.executable, str(verify_script)], check=True)
        return True
    except subprocess.CalledProcessError:
        print("\nDataset verification failed!")
        return False

def run_training():
    """Run model training"""
    paths = get_project_paths()
    train_script = paths['training'] / 'train_model.py'
    
    print("\n" + "="*60)
    print("Starting model training...")
    print("="*60 + "\n")
    
    try:
        result = subprocess.run([sys.executable, str(train_script)], check=True)
        print("\n" + "="*60)
        print("✓ Training completed successfully!")
        print("="*60)
        print(f"\nResults saved to: {paths['training']}")
        print("Check the following files:")
        print(f"  - training_history.json")
        print(f"  - test_metrics.json")
        print(f"  - training_curves.png")
        print(f"  - trained_model.pt")
        return True
    except subprocess.CalledProcessError as e:
        print("\n" + "="*60)
        print("✗ Training failed!")
        print("="*60)
        return False

def show_menu():
    """Display main menu"""
    print("\n" + "="*60)
    print("BRAIN TUMOR DETECTION - TRAINING MENU")
    print("="*60)
    print("\n1. Verify Dataset Setup")
    print("2. Run Full Training")
    print("3. Verify Dataset + Run Training")
    print("4. Exit")
    print("\n" + "="*60)

def main():
    """Main menu loop"""
    paths = get_project_paths()
    
    # Check if training folder exists
    if not paths['training'].exists():
        print(f"\n✗ ERROR: Training folder not found at {paths['training']}")
        sys.exit(1)
    
    while True:
        show_menu()
        choice = input("\nSelect option (1-4): ").strip()
        
        if choice == '1':
            if verify_dataset():
                input("\nPress Enter to continue...")
            else:
                input("\nDataset verification failed. Press Enter to continue...")
        
        elif choice == '2':
            if run_training():
                input("\nPress Enter to continue...")
            else:
                print("\nTo fix training issues:")
                print("  1. Run option 1 to verify dataset")
                print("  2. Check dataset folder has .mat files")
                print("  3. Run: pip install -r requirements.txt")
                input("\nPress Enter to continue...")
        
        elif choice == '3':
            if verify_dataset():
                input("\nDataset verified. Starting training...\n")
                if run_training():
                    input("\nPress Enter to exit...")
                    break
                else:
                    input("\nPress Enter to continue...")
            else:
                print("\nPlease fix dataset issues before training.")
                input("Press Enter to continue...")
        
        elif choice == '4':
            print("\nExiting...")
            break
        
        else:
            print("\n✗ Invalid option. Please select 1-4.")

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nExiting...")
        sys.exit(0)
