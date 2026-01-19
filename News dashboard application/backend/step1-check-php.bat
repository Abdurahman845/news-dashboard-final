@echo off
echo ========================================
echo STEP 1: Check if PHP exists
echo ========================================
echo.

if exist "php-8.3.27-nts-Win32-vs16-x64\php.exe" (
    echo [OK] Found: php-8.3.27-nts-Win32-vs16-x64\php.exe
    goto :test_php
) else (
    echo [FAIL] php-8.3.27-nts-Win32-vs16-x64\php.exe NOT FOUND
)

if exist "php-8.3.4\php.exe" (
    echo [OK] Found: php-8.3.4\php.exe
    goto :test_php
) else (
    echo [FAIL] php-8.3.4\php.exe NOT FOUND
)

echo.
echo ERROR: No PHP found!
pause
exit

:test_php
echo.
echo Testing PHP version...
if exist "php-8.3.27-nts-Win32-vs16-x64\php.exe" (
    php-8.3.27-nts-Win32-vs16-x64\php.exe --version
) else (
    php-8.3.4\php.exe --version
)
echo.
echo If you see PHP version above, Step 1 is OK!
pause

