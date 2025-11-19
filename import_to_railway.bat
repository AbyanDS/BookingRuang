@echo off
REM Script untuk import database ke Railway MySQL
REM 
REM Cara pakai:
REM 1. Double click file ini, atau
REM 2. Jalankan dari CMD (bukan PowerShell): import_to_railway.bat

echo ========================================
echo Railway MySQL Import Script
echo ========================================
echo.
echo Host: metro.proxy.rlwy.net
echo Port: 39338
echo User: root
echo Database: railway
echo.
echo Importing database.sql...
echo.

C:\xampp2\mysql\bin\mysql.exe -h metro.proxy.rlwy.net -u root -pMClfPGdILKmmIxLDjwgZesirmxvnXqiQ --port=39338 --protocol=TCP railway < database.sql

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo SUCCESS! Database imported successfully
    echo ========================================
    echo.
    echo Now importing default data...
    echo.
    
    C:\xampp2\mysql\bin\mysql.exe -h metro.proxy.rlwy.net -u root -pMClfPGdILKmmIxLDjwgZesirmxvnXqiQ --port=39338 --protocol=TCP railway < insert_default_data.sql
    
    if %errorlevel% equ 0 (
        echo.
        echo ========================================
        echo SUCCESS! Default data imported
        echo ========================================
        echo.
        echo Login credentials:
        echo - Admin: admin / password
        echo - Petugas: petugas / password
        echo - User: user / password
        echo.
    ) else (
        echo.
        echo ERROR: Failed to import default data
        echo.
    )
) else (
    echo.
    echo ========================================
    echo ERROR: Failed to import database
    echo ========================================
    echo.
    echo Troubleshooting:
    echo 1. Check your internet connection
    echo 2. Verify Railway MySQL is running
    echo 3. Check credentials are correct
    echo 4. Try using TablePlus or DBeaver instead
    echo.
)

pause
