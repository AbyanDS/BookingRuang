# PowerShell Script untuk Import Database ke Railway MySQL
# Karena mysql client XAMPP tidak support caching_sha2_password

param(
    [string]$SqlFile = "database_railway.sql"
)

$Host.UI.RawUI.WindowTitle = "Railway MySQL Import Script"

Write-Host "=" -NoNewline -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor Cyan
Write-Host "  Railway MySQL Import via PowerShell" -ForegroundColor Yellow
Write-Host "=" -NoNewline -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor Cyan
Write-Host ""

# Railway MySQL Connection Details
$DbHost = "metro.proxy.rlwy.net"
$DbPort = "39338"
$DbUser = "root"
$DbPassword = "MClfPGdILKmmIxLDjwgZesirmxvnXqiQ"
$DbName = "railway"

Write-Host "Connection Details:" -ForegroundColor Green
Write-Host "  Host     : $DbHost" -ForegroundColor Gray
Write-Host "  Port     : $DbPort" -ForegroundColor Gray
Write-Host "  User     : $DbUser" -ForegroundColor Gray
Write-Host "  Database : $DbName" -ForegroundColor Gray
Write-Host ""

# Check if SQL file exists
if (-not (Test-Path $SqlFile)) {
    Write-Host "ERROR: File '$SqlFile' tidak ditemukan!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Pastikan file ada di folder:" -ForegroundColor Yellow
    Write-Host "  $(Get-Location)" -ForegroundColor Gray
    Write-Host ""
    pause
    exit 1
}

Write-Host "Reading SQL file: $SqlFile" -ForegroundColor Green
$SqlContent = Get-Content -Path $SqlFile -Raw -Encoding UTF8
Write-Host "  File size: $($SqlContent.Length) characters" -ForegroundColor Gray
Write-Host ""

Write-Host "Attempting connection to Railway MySQL..." -ForegroundColor Yellow
Write-Host ""

# Try using mysql.exe with different approaches
$MySqlExe = "C:\xampp2\mysql\bin\mysql.exe"

if (Test-Path $MySqlExe) {
    Write-Host "Method 1: Using MySQL Client (might fail due to auth plugin)" -ForegroundColor Cyan
    Write-Host ""
    
    # Create temp batch file to avoid PowerShell redirection issues
    $TempBatch = [System.IO.Path]::GetTempFileName() + ".bat"
    $BatchContent = @"
@echo off
cd /d "$PWD"
"$MySqlExe" -h $DbHost -P $DbPort -u $DbUser -p$DbPassword --default-auth=mysql_native_password $DbName < "$SqlFile"
exit /b %errorlevel%
"@
    Set-Content -Path $TempBatch -Value $BatchContent -Encoding ASCII
    
    # Execute
    $Process = Start-Process -FilePath "cmd.exe" -ArgumentList "/c `"$TempBatch`"" -Wait -PassThru -NoNewWindow
    Remove-Item $TempBatch -Force
    
    if ($Process.ExitCode -eq 0) {
        Write-Host ""
        Write-Host "=" -NoNewline -ForegroundColor Green
        Write-Host ("=" * 60) -ForegroundColor Green
        Write-Host "  SUCCESS! Database imported successfully!" -ForegroundColor Green
        Write-Host "=" -NoNewline -ForegroundColor Green
        Write-Host ("=" * 60) -ForegroundColor Green
        Write-Host ""
        Write-Host "Default Login:" -ForegroundColor Yellow
        Write-Host "  Admin   : admin / password" -ForegroundColor Gray
        Write-Host "  Petugas : petugas / password" -ForegroundColor Gray
        Write-Host ""
        Write-Host "PENTING: Ubah password setelah login pertama!" -ForegroundColor Red
        Write-Host ""
        pause
        exit 0
    } else {
        Write-Host ""
        Write-Host "=" -NoNewline -ForegroundColor Red
        Write-Host ("=" * 60) -ForegroundColor Red
        Write-Host "  FAILED with MySQL client" -ForegroundColor Red
        Write-Host "=" -NoNewline -ForegroundColor Red
        Write-Host ("=" * 60) -ForegroundColor Red
        Write-Host ""
    }
}

Write-Host "MySQL client failed (authentication plugin issue)" -ForegroundColor Red
Write-Host ""
Write-Host "=" -NoNewline -ForegroundColor Yellow
Write-Host ("=" * 60) -ForegroundColor Yellow
Write-Host "  Alternative Solutions:" -ForegroundColor Yellow
Write-Host "=" -NoNewline -ForegroundColor Yellow
Write-Host ("=" * 60) -ForegroundColor Yellow
Write-Host ""

Write-Host "1. Railway Dashboard Query Editor (RECOMMENDED)" -ForegroundColor Cyan
Write-Host "   - Buka: https://railway.app" -ForegroundColor Gray
Write-Host "   - MySQL service -> Data -> Query" -ForegroundColor Gray
Write-Host "   - Copy paste isi file: $SqlFile" -ForegroundColor Gray
Write-Host "   - Click Run" -ForegroundColor Gray
Write-Host ""

Write-Host "2. Install TablePlus (Free GUI Client)" -ForegroundColor Cyan
Write-Host "   - Download: https://tableplus.com" -ForegroundColor Gray
Write-Host "   - Connect dengan credentials di atas" -ForegroundColor Gray
Write-Host "   - Import SQL file" -ForegroundColor Gray
Write-Host ""

Write-Host "3. Install DBeaver (Free & Open Source)" -ForegroundColor Cyan
Write-Host "   - Download: https://dbeaver.io" -ForegroundColor Gray
Write-Host "   - Support MySQL 8.0+ authentication" -ForegroundColor Gray
Write-Host ""

Write-Host "4. Install MySQL 8.0+ Command Line Tools" -ForegroundColor Cyan
Write-Host "   - Download: https://dev.mysql.com/downloads/shell/" -ForegroundColor Gray
Write-Host "   - Support modern authentication methods" -ForegroundColor Gray
Write-Host ""

Write-Host "=" -NoNewline -ForegroundColor Yellow
Write-Host ("=" * 60) -ForegroundColor Yellow
Write-Host ""
Write-Host "File SQL sudah siap: $SqlFile" -ForegroundColor Green
Write-Host "Gunakan salah satu cara di atas untuk import!" -ForegroundColor Yellow
Write-Host ""

# Ask if user wants to open Railway dashboard
Write-Host "Buka Railway Dashboard sekarang? (Y/N): " -NoNewline -ForegroundColor Cyan
$Answer = Read-Host

if ($Answer -eq 'Y' -or $Answer -eq 'y') {
    Start-Process "https://railway.app"
    Write-Host "Browser opened. Login dan ikuti langkah di atas." -ForegroundColor Green
}

Write-Host ""
pause
