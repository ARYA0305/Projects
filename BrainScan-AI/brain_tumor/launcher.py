#!/usr/bin/env python3
"""
Launcher script for Brain Tumor Detection App
This script automatically detects and uses the correct Python environment.
"""

import os
import sys
import subprocess

def run_app():
    """Run the Flask app using the best available environment."""

    app_dir = os.path.dirname(os.path.abspath(__file__))
    app_path = os.path.join(app_dir, 'app.py')

    # Try virtual environment first
    venv_python = os.path.join(app_dir, 'venv', 'Scripts', 'python.exe')
    if os.path.exists(venv_python):
        print("Using virtual environment...")
        subprocess.run([venv_python, app_path])
        return

    # Try global Python
    try:
        print("Using global Python environment...")
        subprocess.run([sys.executable, app_path])
        return
    except subprocess.CalledProcessError as e:
        print(f"Error running with global Python: {e}")
        print("\nTo fix this, install dependencies:")
        print("pip install -r requirements.txt")
        return

if __name__ == "__main__":
    run_app()