@echo off
REM Brain Tumor Detection - Training Menu Launcher
REM Interactive menu for training and verification

chcp 65001 >nul
color 0A

echo.
echo ============================================================
echo BRAIN TUMOR DETECTION - TRAINING MENU
echo ============================================================
echo.

REM Try virtual environment first
if exist "venv310\Scripts\python.exe" (
    echo Using virtual environment...
    call venv310\Scripts\activate.bat
    python training\run_training_menu.py
) else (
    echo Using system Python...
    python training\run_training_menu.py
)

if %errorlevel% neq 0 (
    echo.
    echo ERROR: Failed to launch training menu
    echo.
    echo Make sure you:
    echo 1. Have Python installed (python.org)
    echo 2. Installed dependencies: pip install -r requirements.txt
    echo 3. Have dataset files in brain_tumor/dataset/ folder
    echo.
    pause
)
