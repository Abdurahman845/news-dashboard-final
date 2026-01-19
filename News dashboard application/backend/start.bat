@echo off
cd /d "%~dp0"
echo ========================================
echo  News Dashboard Backend Server
echo ========================================
echo.
echo Starting server at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.
if exist "C:\Users\good\Downloads\php-8.4.14-Win32-vs17-x64\php.exe" (
    "C:\Users\good\Downloads\php-8.4.14-Win32-vs17-x64\php.exe" -S localhost:8000 -t . api.php
) else if exist "php-8.3.4\php.exe" (
    php-8.3.4\php.exe -S localhost:8000 -t . api.php
) else if exist "php-8.3.27-nts-Win32-vs16-x64\php.exe" (
    php-8.3.27-nts-Win32-vs16-x64\php.exe -S localhost:8000 -t . api.php
) else (
    echo ERROR: PHP not found
    pause
    exit
)
pause

