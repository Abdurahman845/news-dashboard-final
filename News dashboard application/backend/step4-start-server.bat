@echo off
echo ========================================
echo STEP 4: Start the server
echo ========================================
echo.

if exist "php-8.3.27-nts-Win32-vs16-x64\php.exe" (
    echo Starting with php-8.3.27...
    php-8.3.27-nts-Win32-vs16-x64\php.exe -S localhost:8000 -t . api.php
) else if exist "php-8.3.4\php.exe" (
    echo Starting with php-8.3.4...
    php-8.3.4\php.exe -S localhost:8000 -t . api.php
) else (
    echo ERROR: PHP not found!
    pause
    exit
)

