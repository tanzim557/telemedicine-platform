@echo off
title TeleMedicine - Local Whisper Server
color 0A
echo.
echo ===================================================
echo   TeleMedicine - Local Whisper Large Model Server
echo ===================================================
echo.
echo  Starting Whisper Large model server...
echo  First run will download ~2.9 GB model (one time only).
echo  After that it starts in ~30 seconds.
echo.
echo  Server will be available at: http://127.0.0.1:5001
echo  Keep this window open while using the app.
echo.

cd /d "c:\Users\LIMA\Desktop\TELE MEDICINE"

where python >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    set PYTHON_PATH=C:\Users\LIMA\AppData\Local\Python\pythoncore-3.14-64\python.exe
) else (
    set PYTHON_PATH=python
)

%PYTHON_PATH% whisper_server.py

pause
