@echo off
echo ========================================
echo STEP 3: Check SQLite extension
echo ========================================
echo.
"C:\Users\good\Downloads\php-8.4.14-Win32-vs17-x64\php.exe" -r "echo extension_loaded('pdo_sqlite') ? 'YES - SQLite works!' : 'NO - Need to enable SQLite';"
echo.
pause

