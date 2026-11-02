@echo off
REM Brain Tumor Detection - Training Script
REM This script runs the model training process

setlocal enabledelayedexpansion

echo.
echo ============================================================
echo BRAIN TUMOR DETECTION - MODEL TRAINING
echo ============================================================
echo.

REM Check if we're in venv310
if exist "venv310\Scripts\python.exe" (
    echo Using virtual environment...
    call venv310\Scripts\activate.bat
    python training\train_model.py
) else (
    echo Using system Python...
    python training\train_model.py
)

if %errorlevel% neq 0 (
    echo.
    echo ERROR: Training failed!
    echo.
    echo To fix this:
    echo 1. Make sure dependencies are installed: pip install -r requirements.txt
    echo 2. Check that dataset folder exists with .mat files
    echo.
    pause
) else (
    echo.
    echo ✓ Training completed successfully!
    echo Results saved to: training\
    echo.
    pause
)
