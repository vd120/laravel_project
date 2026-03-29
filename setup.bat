@echo off
REM ============================================
REM Nexus - Complete Project Setup Script
REM For Windows CMD - Full MySQL Support
REM ============================================

SETLOCAL EnableDelayedExpansion

REM Global variables
SET "DB_CONNECTION="
SET "DB_NAME="
SET "DB_USER="
SET "DB_PASS="
SET "DB_HOST=127.0.0.1"
SET "DB_PORT=3306"

REM ============================================
REM Helper Functions
REM ============================================

:print_header
echo.
echo Nexus - Complete Setup Script (Windows)
echo.
goto :eof

:print_status
echo   * %~1
goto :eof

:print_success
echo   OK %~1
goto :eof

:print_error
echo   X %~1
goto :eof

:print_warning
echo   ! %~1
goto :eof

:print_info
echo   i %~1
goto :eof

:cleanup_and_exit
if %~1 neq 0 (
    echo.
    call :print_error "Setup failed! Please check the errors above."
    echo   Troubleshooting tips:
    echo     1. Check if all requirements are installed
    echo     2. Verify database credentials are correct
    echo     3. Ensure PHP extensions are enabled
    echo     4. Check storage\logs\laravel.log for errors
    echo.
)
exit /b %~1

REM ============================================
REM Step 1: Check System Requirements
REM ============================================

:check_requirements
call :print_header
echo Step 1: Checking System Requirements
echo ----------------------------------------

REM Check PHP
call :print_status "Checking PHP..."
where php >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=2" %%i in ('php -v ^| findstr /r "^[0-9]"') do set PHP_VER=%%i
    call :print_success "PHP !PHP_VER! installed"
    
    REM Check PHP version (need 8.2+)
    for /f "tokens=1,2 delims=." %%a in ("!PHP_VER!") do (
        if %%a LSS 8 (
            call :print_error "PHP 8.2 or higher is required! You have !PHP_VER!"
            call :cleanup_and_exit 1
        )
        if %%a EQU 8 (
            if %%b LSS 2 (
                call :print_error "PHP 8.2 or higher is required! You have !PHP_VER!"
                call :cleanup_and_exit 1
            )
        )
    )
) else (
    call :print_error "PHP is not installed!"
    echo   Install from: https://windows.php.net/download/
    echo   Ensure PHP is added to PATH
    pause
    exit /b 1
)

REM Check required PHP extensions
echo.
echo   Checking PHP extensions...
SET "MISSING_EXT="

for %%e in (mbstring xml curl zip openssl pdo json tokenizer bcmath mysql gd) do (
    php -m 2>nul | findstr /i "%%e" >nul 2>&1
    if !errorlevel! equ 0 (
        call :print_success "PHP extension: %%e"
    ) else (
        call :print_error "PHP extension: %%e (MISSING)"
        SET "MISSING_EXT=!MISSING_EXT! %%e"
    )
)

if defined MISSING_EXT (
    echo.
    call :print_error "Missing PHP extensions:%MISSING_EXT%"
    echo   Enable extensions in php.ini or reinstall PHP with: gd extension
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

REM Check MySQL client (optional)
call :print_status "Checking MySQL client..."
where mysql >nul 2>&1
if %errorlevel% equ 0 (
    call :print_success "MySQL client installed"
) else (
    call :print_warning "MySQL client not found (optional)"
)

goto :eof

REM ============================================
REM Step 2: Install Dependencies
REM ============================================

:install_dependencies
echo.
echo Step 2: Installing PHP Dependencies
echo ----------------------------------------
call :print_status "Running composer install..."
call composer install --no-interaction --prefer-dist --optimize-autoloader
if %errorlevel% equ 0 (
    call :print_success "PHP dependencies installed"
) else (
    call :print_error "Failed to install PHP dependencies"
    call :cleanup_and_exit 1
)

echo.
echo Step 3: Installing JavaScript Dependencies
echo ----------------------------------------
call :print_status "Running npm install..."
call npm install
if %errorlevel% equ 0 (
    call :print_success "JavaScript dependencies installed"
) else (
    call :print_warning "First npm install attempt failed, retrying with --legacy-peer-deps..."
    call npm install --legacy-peer-deps
    if %errorlevel% equ 0 (
        call :print_success "JavaScript dependencies installed (with --legacy-peer-deps)"
    ) else (
        call :print_error "Failed to install JavaScript dependencies"
        call :cleanup_and_exit 1
    )
)

goto :eof

REM ============================================
REM Step 3: Setup Environment
REM ============================================

:setup_environment
echo.
echo Step 4: Setting Up Environment
echo ----------------------------------------

REM Create .env from .env.example
if not exist ".env" (
    call :print_status "Creating .env file..."
    copy .env.example .env >nul
    call :print_success ".env file created"
) else (
    call :print_status ".env file already exists"
    set /p OVERWRITE="  Overwrite existing .env? (y/n) [n]: "
    if "!OVERWRITE!"=="y" (
        copy .env.example .env >nul
        call :print_success ".env file overwritten"
    )
)

REM Generate application key
call :print_status "Generating application key..."
call php artisan key:generate
if %errorlevel% equ 0 (
    call :print_success "Application key generated"
) else (
    call :print_error "Failed to generate application key"
    call :cleanup_and_exit 1
)

goto :eof

REM ============================================
REM Step 4: Database Configuration
REM ============================================

:setup_sqlite
call :print_status "Setting up SQLite database..."

REM Update .env for SQLite
powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' | Set-Content .env"

REM Create database directory and file
if not exist "database" mkdir database
type nul > database\database.sqlite
call :print_success "SQLite database created at database\database.sqlite"
SET "DB_CONNECTION=sqlite"
goto :eof

:setup_mysql
call :print_status "Setting up MySQL database..."
echo.

REM Get database host
set /p DB_HOST_INPUT="  Database host [127.0.0.1]: "
if "!DB_HOST_INPUT!"=="" (SET "DB_HOST=127.0.0.1") else (SET "DB_HOST=!DB_HOST_INPUT!")

REM Get database port
set /p DB_PORT_INPUT="  Database port [3306]: "
if "!DB_PORT_INPUT!"=="" (SET "DB_PORT=3306") else (SET "DB_PORT=!DB_PORT_INPUT!")

REM Ask if user wants to create new database or use existing
echo.
echo   Database Setup:
echo   1) Create new database
echo   2) Use existing database
echo.
set /p DB_SETUP_CHOICE="  Enter choice [1-2]: "

if "!DB_SETUP_CHOICE!"=="1" (
    REM Create new database
    echo.
    set /p DB_NAME="  New database name: "
    if "!DB_NAME!"=="" (
        call :print_error "Database name is required"
        pause
        exit /b 1
    )

    set /p DB_USER_ADMIN="  Database username (for creating DB) [root]: "
    if "!DB_USER_ADMIN!"=="" set DB_USER_ADMIN=root

    set /p DB_PASS_ADMIN="  Database password (for admin user): "

    set /p DB_USER_NEW="  Database user to create [same as db name]: "
    if "!DB_USER_NEW!"=="" set DB_USER_NEW=!DB_NAME!

    set /p DB_PASS_NEW="  Password for new database user: "

    REM Test MySQL connection
    call :print_status "Testing MySQL connection..."
    mysql -h !DB_HOST! -P !DB_PORT! -u !DB_USER_ADMIN! -p!DB_PASS_ADMIN! -e "SELECT 1;" >nul 2>&1
    if !errorlevel! neq 0 (
        call :print_error "Cannot connect to MySQL with provided credentials!"
        echo   Please check:
        echo     1. MySQL server is running
        echo     2. Host and port are correct
        echo     3. Username and password are valid
        pause
        exit /b 1
    )
    call :print_success "MySQL connection successful"

    REM Create database
    call :print_status "Creating database '!DB_NAME!'..."
    mysql -h !DB_HOST! -P !DB_PORT! -u !DB_USER_ADMIN! -p!DB_PASS_ADMIN! -e "CREATE DATABASE IF NOT EXISTS \`"!DB_NAME!\`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >nul 2>&1
    if !errorlevel! neq 0 (
        call :print_error "Failed to create database"
        call :cleanup_and_exit 1
    )
    call :print_success "Database '!DB_NAME!' created"

    REM Create user and grant privileges
    call :print_status "Creating user '!DB_USER_NEW!' and granting privileges..."
    mysql -h !DB_HOST! -P !DB_PORT! -u !DB_USER_ADMIN! -p!DB_PASS_ADMIN! -e "CREATE USER IF NOT EXISTS '\`"!DB_USER_NEW!\`"'@'\`"%\`"' IDENTIFIED BY '\`"!DB_PASS_NEW!\`';" >nul 2>&1 || true
    mysql -h !DB_HOST! -P !DB_PORT! -u !DB_USER_ADMIN! -p!DB_PASS_ADMIN! -e "GRANT ALL PRIVILEGES ON \`"!DB_NAME!\`".* TO '\`"!DB_USER_NEW!\`"'@'\`"%\`';" >nul 2>&1 || true
    mysql -h !DB_HOST! -P !DB_PORT! -u !DB_USER_ADMIN! -p!DB_PASS_ADMIN! -e "FLUSH PRIVILEGES;" >nul 2>&1 || true
    call :print_success "User created and privileges granted"

    SET "DB_USER=!DB_USER_NEW!"
    SET "DB_PASS=!DB_PASS_NEW!"

) else if "!DB_SETUP_CHOICE!"=="2" (
    REM Use existing database
    echo.
    set /p DB_NAME="  Existing database name: "
    if "!DB_NAME!"=="" (
        call :print_error "Database name is required"
        pause
        exit /b 1
    )

    set /p DB_USER="  Database username: "
    if "!DB_USER!"=="" (
        call :print_error "Database username is required"
        pause
        exit /b 1
    )

    set /p DB_PASS="  Database password: "

    REM Test connection
    call :print_status "Testing MySQL connection..."
    mysql -h !DB_HOST! -P !DB_PORT! -u !DB_USER! -p!DB_PASS! -e "USE \`"!DB_NAME!\`";" >nul 2>&1
    if !errorlevel! neq 0 (
        call :print_error "Cannot connect to MySQL database!"
        echo   Please check:
        echo     1. MySQL server is running
        echo     2. Database '!DB_NAME!' exists
        echo     3. Username and password are correct
        echo     4. User has privileges on the database
        pause
        exit /b 1
    )
    call :print_success "MySQL connection successful"

) else (
    call :print_error "Invalid choice. Please run the script again."
    pause
    exit /b 1
)

REM Update .env for MySQL
powershell -Command "(Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=mysql' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '^# DB_HOST=.*','DB_HOST=!DB_HOST!' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '^# DB_PORT=.*','DB_PORT=!DB_PORT!' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '^# DB_DATABASE=.*','DB_DATABASE=!DB_NAME!' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '^# DB_USERNAME=.*','DB_USERNAME=!DB_USER!' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace '^# DB_PASSWORD=.*','DB_PASSWORD=!DB_PASS!' | Set-Content .env"

REM Clear config cache
call php artisan config:clear >nul

call :print_success "MySQL configuration saved to .env"
SET "DB_CONNECTION=mysql"
goto :eof

:configure_database
echo.
echo Step 5: Database Configuration
echo ----------------------------------------
echo.
echo   Select database type:
echo   1) SQLite (recommended for development/testing)
echo   2) MySQL/MariaDB (recommended for production)
echo.
set /p DB_CHOICE="  Enter choice [1-2]: "

if "%DB_CHOICE%"=="1" (
    call :setup_sqlite
) else if "%DB_CHOICE%"=="2" (
    call :setup_mysql
) else (
    call :print_error "Invalid choice. Defaulting to SQLite."
    call :setup_sqlite
)

goto :eof

REM ============================================
REM Step 5: Run Migrations
REM ============================================

:run_migrations
echo.
echo Step 6: Running Database Migrations
echo ----------------------------------------

call :print_status "Clearing configuration cache..."
call php artisan config:clear >nul
call php artisan cache:clear >nul

call :print_status "Running migrations..."
call php artisan migrate --force
if %errorlevel% equ 0 (
    call :print_success "Database migrations completed successfully"
) else (
    call :print_error "Failed to run migrations"
    echo.
    echo   Troubleshooting:
    echo     1. Check database credentials in .env
    echo     2. Ensure database exists and is accessible
    echo     3. Check storage\logs\laravel.log for errors
    echo     4. Run 'php artisan migrate:status' to see migration status
    echo.
    call :cleanup_and_exit 1
)

goto :eof

REM ============================================
REM Step 6: Create Admin User
REM ============================================

:create_admin_user
echo.
echo Step 7: Creating Admin User
echo ----------------------------------------

REM Check if AdminUserSeeder exists
if exist "database\seeders\AdminUserSeeder.php" (
    call :print_status "Running AdminUserSeeder..."
    call php artisan db:seed --class=AdminUserSeeder --force
    call :print_success "Admin user created"
) else (
    call :print_status "Creating admin user directly..."
    
    REM Create admin user using tinker
    call php artisan tinker --execute="^< ?php $user = \App\Models\User::where('email', 'admin@example.com')-^>first(); if (!$user) { $user = \App\Models\User::create(['name' =^> 'Admin User', 'email' =^> 'admin@example.com', 'password' =^> bcrypt('admin123'), 'email_verified_at' =^> now(), 'is_admin' =^> true, 'username' =^> 'admin', ]); \App\Models\Profile::create(['user_id' =^> $user-^>id]); echo 'Admin user created successfully'; } else { echo 'Admin user already exists'; } ?^>"
    call :print_success "Admin user setup complete"
)

goto :eof

REM ============================================
REM Step 7: Build Frontend
REM ============================================

:build_frontend
echo.
echo Step 8: Building Frontend Assets
echo ----------------------------------------
call :print_status "Building assets with Vite..."

call npm run build
if %errorlevel% equ 0 (
    call :print_success "Frontend assets built successfully"
) else (
    call :print_error "Failed to build frontend assets"
    echo   Try running 'npm install' again and check for errors
    call :cleanup_and_exit 1
)

goto :eof

REM ============================================
REM Step 8: Storage and Permissions
REM ============================================

:setup_storage
echo.
echo Step 9: Setting Up Storage
echo ----------------------------------------

REM Create storage link
call :print_status "Creating storage symbolic link..."
call php artisan storage:link 2>nul
if %errorlevel% equ 0 (
    call :print_success "Storage link created"
) else (
    call :print_status "Storage link may already exist"
)

REM Verify directories
call :print_status "Verifying storage directories..."
if exist "storage" (
    call :print_success "Storage directory verified"
)

if exist "bootstrap\cache" (
    call :print_success "Cache directory verified"
)

goto :eof

REM ============================================
REM Step 9: Final Setup
REM ============================================

:finalize_setup
echo.
echo Step 10: Finalizing Setup
echo ----------------------------------------

call :print_status "Clearing all caches..."
call php artisan config:clear >nul
call php artisan cache:clear >nul
call php artisan view:clear >nul
call php artisan route:clear >nul
call php artisan event:clear >nul
call :print_success "All caches cleared"

REM Optimize (optional)
echo.
set /p OPTIMIZE="  Optimize for production? (y/n) [n]: "
if "!OPTIMIZE!"=="y" (
    call :print_status "Optimizing application..."
    call php artisan optimize
    call :print_success "Application optimized"
)

goto :eof

REM ============================================
REM Summary
REM ============================================

:print_summary
echo.
echo Setup Complete!
echo.
echo Your Nexus project is ready to use!
echo.
echo Database Configuration:
echo   Type: %DB_CONNECTION%
if "%DB_CONNECTION%"=="mysql" (
    echo   Host: %DB_HOST%:%DB_PORT%
    echo   Database: %DB_NAME%
    echo   Username: %DB_USER%
)
echo.
echo Admin Login Credentials:
echo   URL:      http://localhost:8000
echo   Email:    admin@example.com
echo   Password: admin123
echo   Username: admin
echo.
echo Security Notice: Change the default password after login!
echo.
echo To start the development server:
echo   php artisan serve
echo.
echo Useful Commands:
echo   php artisan migrate          - Run migrations
echo   php artisan migrate:fresh    - Reset and run migrations
echo   php artisan db:seed          - Seed database
echo   npm run dev                  - Start Vite dev server
echo   php artisan optimize         - Optimize for production
echo.
echo Enjoy building with Nexus!
echo.
goto :eof

REM ============================================
REM Main Execution
REM ============================================

:main
call :check_requirements
call :install_dependencies
call :setup_environment
call :configure_database
call :run_migrations
call :create_admin_user
call :build_frontend
call :setup_storage
call :finalize_setup
call :print_summary

pause
goto :eof

REM Run main function
call :main
