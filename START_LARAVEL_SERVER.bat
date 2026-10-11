@echo off
title TeleMedicine - Web Server
color 0B
echo.
echo ===================================================
echo   TeleMedicine - Web Server (Laravel)
echo ===================================================
echo.
echo Starting the web application...
echo.

cd /d "c:\Users\LIMA\Desktop\TELE MEDICINE\tele-med-app"
php artisan serve --port=8585

pause
