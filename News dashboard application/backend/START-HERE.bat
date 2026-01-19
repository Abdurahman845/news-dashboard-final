@echo off
cd /d "%~dp0"
echo Starting Backend Server...
echo.
"C:\Users\good\Downloads\php-8.4.14-Win32-vs17-x64\php.exe" -S localhost:8000 -t . api.php

