@echo off
echo ========================================
echo STEP 2: Check if API file exists
echo ========================================
echo.

if exist "api.php" (
    echo [OK] api.php found
    echo File size: 
    dir api.php | find "api.php"
) else (
    echo [FAIL] api.php NOT FOUND
    pause
    exit
)

echo.
echo Step 2 is OK!
pause

