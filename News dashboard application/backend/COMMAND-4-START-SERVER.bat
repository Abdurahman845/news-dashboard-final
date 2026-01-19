@echo off
echo ========================================
echo STEP 4: Starting Backend Server
echo ========================================
echo.
echo Server will run at: http://localhost:8000
echo Press Ctrl+C to stop
echo.
cd /d "C:\Users\good\Desktop\News dashboard application\backend"
"C:\Users\good\Downloads\php-8.4.14-Win32-vs17-x64\php.exe" -S localhost:8000 -t . api.php

