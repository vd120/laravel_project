@echo off
REM ============================================
REM Nexus - Project Setup Script (Windows)
REM For new users who cloned the project
REM ============================================

SETLOCAL EnableDelayedExpansion

REM Colors (ANSI escape codes)
SET "GREEN=[32m"
SET "RED=[31m"
SET "YELLOW=[33m"
SET "CYAN=[36m"
SET "BOLD=[1m"
SET "NC=[0m"

REM Enable ANSI escape codes on Windows 10+
for /F "tokens=4-5 delims=. " %%i in ('ver') do SET VERSION=%%i.%%j
if "%VERSION%" == "10.0" (
    reg add HKCU\Console /v VirtualTerminalLevel /t REG_DWORD /d 1 /f >nul 2>&1
)

echo.
echo ╔════════════════════════════════════════════════════╗
echo ║     Nexus - Setup Script (Windows)                 ║
echo ╚════════════════════════════════════════════════════╝
echo.

REM Function to print status
:print_status
echo   ● %~1
goto :eof

:print_success
echo   ✓ %~1
goto :eof

:print_error
echo   ✗ %~1
goto :eof

REM Check if running in project directory
if not exist "composer.json" (
    call :print_error "Error: composer.json not found!"
    call :print_error "Please run this script from the project root directory."
    pause
    exit /b 1
)

echo %BOLD%Step 1: Checking System Requirements%NC%
echo ────────────────────────────────────────

REM Check PHP
call :print_status "Checking PHP..."
where php >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=2" %%i in ('php -v ^| findstr /r "^[0-9]"') do set PHP_VER=%%i
    call :print_success "PHP !PHP_VER! installed"
) else (
    call :print_error "PHP is not installed!"
    echo   Install from: https://windows.php.net/download/
    pause
    exit /b 1
)

REM Check Composer
call :print_status "Checking Composer..."
where composer >nul 2>&1
if %errorlevel% equ 0 (
    call :print_success "Composer installed"
) else (
    call :print_error "Composer is not installed!"
    echo   Install from: https://getcomposer.org/download/
    pause
    exit /b 1
)

REM Check Node.js
call :print_status "Checking Node.js..."
where node >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=*" %%i in ('node -v') do set NODE_VER=%%i
    call :print_success "Node.js !NODE_VER! installed"
) else (
    call :print_error "Node.js is not installed!"
    echo   Install from: https://nodejs.org/
    pause
    exit /b 1
)

REM Check npm
call :print_status "Checking npm..."
where npm >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=*" %%i in ('npm -v') do set NPM_VER=%%i
    call :print_success "npm !NPM_VER! installed"
) else (
    call :print_error "npm is not installed!"
    pause
    exit /b 1
)

REM Check Git
call :print_status "Checking Git..."
where git >nul 2>&1
if %errorlevel% equ 0 (
    call :print_success "Git installed"
) else (
    call :print_error "Git is not installed!"
    echo   Install from: https://git-scm.com/download/win
    pause
    exit /b 1
)

echo.
echo %BOLD%Step 2: Installing PHP Dependencies%NC%
echo ────────────────────────────────────────
call :print_status "Running composer install..."
call composer install --no-interaction --prefer-dist --optimize-autoloader
if %errorlevel% equ 0 (
    call :print_success "PHP dependencies installed"
) else (
    call :print_error "Failed to install PHP dependencies"
    pause
    exit /b 1
)

echo.
echo %BOLD%Step 3: Installing JavaScript Dependencies%NC%
echo ────────────────────────────────────────
call :print_status "Running npm install..."
call npm install
if %errorlevel% equ 0 (
    call :print_success "JavaScript dependencies installed"
) else (
    call :print_error "Failed to install JavaScript dependencies"
    pause
    exit /b 1
)

echo.
echo %BOLD%Step 4: Setting Up Environment%NC%
echo ────────────────────────────────────────

REM Create .env from .env.example
if not exist ".env" (
    call :print_status "Creating .env file..."
    copy .env.example .env >nul
    call :print_success ".env file created"
) else (
    call :print_status ".env file already exists"
)

REM Generate application key
call :print_status "Generating application key..."
call php artisan key:generate
if %errorlevel% equ 0 (
    call :print_success "Application key generated"
) else (
    call :print_error "Failed to generate application key"
    pause
    exit /b 1
)

echo.
echo %BOLD%Step 5: Setting Up Database%NC%
echo ────────────────────────────────────────
echo.
echo   Select database type:
echo   1) SQLite (recommended for development)
echo   2) MySQL/MariaDB
echo.
set /p DB_CHOICE="  Enter choice [1-2]: "

if "%DB_CHOICE%"=="1" (
    call :print_status "Setting up SQLite database..."
    
    REM Update .env for SQLite
    powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' | Set-Content .env"
    
    REM Create SQLite database file
    if not exist "database" mkdir database
    type nul > database\database.sqlite
    call :print_success "SQLite database created"
    
) else if "%DB_CHOICE%"=="2" (
    call :print_status "Setting up MySQL database..."
    echo.
    set /p DB_NAME="  Database name [laravel]: "
    if "!DB_NAME!"=="" set DB_NAME=laravel
    
    set /p DB_USER="  Database username [root]: "
    if "!DB_USER!"=="" set DB_USER=root
    
    set /p DB_PASS="  Database password: "
    
    REM Update .env for MySQL
    powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=mysql' | Set-Content .env"
    powershell -Command "(Get-Content .env) -replace '^# DB_HOST=.*','DB_HOST=127.0.0.1' | Set-Content .env"
    powershell -Command "(Get-Content .env) -replace '^# DB_PORT=.*','DB_PORT=3306' | Set-Content .env"
    powershell -Command "(Get-Content .env) -replace '^# DB_DATABASE=.*','DB_DATABASE=!DB_NAME!' | Set-Content .env"
    powershell -Command "(Get-Content .env) -replace '^# DB_USERNAME=.*','DB_USERNAME=!DB_USER!' | Set-Content .env"
    powershell -Command "(Get-Content .env) -replace '^# DB_PASSWORD=.*','DB_PASSWORD=!DB_PASS!' | Set-Content .env"
    
    call :print_success "MySQL configuration saved"
    call :print_status "Please create the database '!DB_NAME!' in MySQL before running migrations"
    echo   CREATE DATABASE !DB_NAME!;
    
) else (
    call :print_error "Invalid choice. Using SQLite by default."
    if not exist "database" mkdir database
    type nul > database\database.sqlite
    powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' | Set-Content .env"
)

echo.
echo %BOLD%Step 6: Running Database Migrations%NC%
echo ────────────────────────────────────────
call :print_status "Running migrations..."
call php artisan migrate --force
if %errorlevel% equ 0 (
    call :print_success "Database migrations completed"
) else (
    call :print_error "Failed to run migrations"
    echo   Make sure database is properly configured
    pause
    exit /b 1
)

echo.
echo %BOLD%Step 7: Seeding Admin User%NC%
echo ────────────────────────────────────────
call :print_status "Running admin user seeder..."
call php artisan db:seed --class=AdminUserSeeder --force
if %errorlevel% equ 0 (
    call :print_success "Admin user created"
) else (
    call :print_status "Admin user may already exist"
)

echo.
echo %BOLD%Step 8: Building Frontend Assets%NC%
echo ────────────────────────────────────────
call :print_status "Building assets with npm..."
call npm run build
if %errorlevel% equ 0 (
    call :print_success "Frontend assets built"
) else (
    call :print_error "Failed to build assets"
    pause
    exit /b 1
)

echo.
echo %BOLD%Step 9: Creating Storage Links%NC%
echo ────────────────────────────────────────
call :print_status "Creating storage link..."
call php artisan storage:link
if %errorlevel% equ 0 (
    call :print_success "Storage link created"
) else (
    call :print_status "Storage link may already exist"
)

echo.
echo %BOLD%Step 10: Clearing Caches%NC%
echo ────────────────────────────────────────
call :print_status "Clearing caches..."
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear
call php artisan route:clear
call :print_success "Caches cleared"

echo.
echo ════════════════════════════════════════
echo   Setup Complete!
echo ════════════════════════════════════════
echo.
echo Your Nexus project is ready!
echo.
echo Admin Login Credentials:
echo   Email:    admin@example.com
echo   Password: admin123
echo   Username: admin
echo.
echo To start the development server:
echo   php artisan serve
echo.
echo Or to start with tunnel ^(for public URL^):
echo   .\start-tunnel.sh ^(requires WSL or Git Bash^)^
echo.
echo Enjoy!
echo.
pause
