@echo off
echo ========================================
echo STEP 3: Test PHP SQLite extension
echo ========================================
echo.

if exist "php-8.3.27-nts-Win32-vs16-x64\php.exe" (
    php-8.3.27-nts-Win32-vs16-x64\php.exe -r "echo extension_loaded('pdo_sqlite') ? 'YES - SQLite works!' : 'NO - SQLite missing';"
) else (
    php-8.3.4\php.exe -r "echo extension_loaded('pdo_sqlite') ? 'YES - SQLite works!' : 'NO - SQLite missing';"
)

echo.
echo If you see 'YES', Step 3 is OK!
echo If you see 'NO', we need to enable SQLite extension.
pause

