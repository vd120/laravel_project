# ============================================
# Nexus - Complete Project Setup Script
# For Windows PowerShell - Full MySQL Support
# ============================================

#Requires -Version 5.0

# Colors
$GREEN = [ConsoleColor]::Green
$RED = [ConsoleColor]::Red
$YELLOW = [ConsoleColor]::Yellow
$CYAN = [ConsoleColor]::Cyan
$BLUE = [ConsoleColor]::Blue
$WHITE = [ConsoleColor]::White
$GRAY = [ConsoleColor]::Gray

# Global variables
$script:DB_CONNECTION = ""
$script:DB_NAME = ""
$script:DB_USER = ""
$script:DB_PASS = ""
$script:DB_HOST = "127.0.0.1"
$script:DB_PORT = "3306"

# ============================================
# Helper Functions
# ============================================

function Write-Header {
    Write-Host ""
    Write-Host "╔════════════════════════════════════════════════════╗" -ForegroundColor $CYAN
    Write-Host "║     Nexus - Complete Setup Script (PowerShell)     ║" -ForegroundColor $CYAN
    Write-Host "╚════════════════════════════════════════════════════╝" -ForegroundColor $CYAN
    Write-Host ""
}

function Write-Status {
    param([string]$Message)
    Write-Host "  ● $Message" -ForegroundColor $CYAN
}

function Write-Success {
    param([string]$Message)
    Write-Host "  ✓ $Message" -ForegroundColor $GREEN
}

function Write-Error-Custom {
    param([string]$Message)
    Write-Host "  ✗ $Message" -ForegroundColor $RED
}

function Write-Warning-Custom {
    param([string]$Message)
    Write-Host "  ⚠ $Message" -ForegroundColor $YELLOW
}

function Write-Info {
    param([string]$Message)
    Write-Host "  ℹ $Message" -ForegroundColor $BLUE
}

function Test-Command {
    param([string]$Command)
    return $null -ne (Get-Command $Command -ErrorAction SilentlyContinue)
}

function Get-MySQLConnection {
    param(
        [string]$Host,
        [string]$Port,
        [string]$User,
        [string]$Password,
        [string]$Database = ""
    )
    
    $mysqlPath = Get-Command mysql -ErrorAction SilentlyContinue
    if ($null -eq $mysqlPath) {
        return $false
    }
    
    $query = if ($Database) { "USE `$Database`;" } else { "SELECT 1;" }
    $processInfo = New-Object System.Diagnostics.ProcessStartInfo
    $processInfo.FileName = $mysqlPath.Source
    $processInfo.Arguments = "-h $Host -P $Port -u $User -p$Password -e `"$query`""
    $processInfo.RedirectStandardOutput = $true
    $processInfo.RedirectStandardError = $true
    $processInfo.UseShellExecute = $false
    $processInfo.CreateNoWindow = $true
    
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $processInfo
    $process.Start() | Out-Null
    $process.WaitForExit(5000)
    
    return ($process.ExitCode -eq 0)
}

function Create-MySQLDatabase {
    param(
        [string]$Host,
        [string]$Port,
        [string]$AdminUser,
        [string]$AdminPassword,
        [string]$DatabaseName,
        [string]$NewUser,
        [string]$NewPassword
    )
    
    $mysqlPath = Get-Command mysql -ErrorAction SilentlyContinue
    if ($null -eq $mysqlPath) {
        Write-Error-Custom "MySQL client not found. Please install MySQL or MariaDB."
        return $false
    }
    
    # Create database
    $createDbQuery = "CREATE DATABASE IF NOT EXISTS \`"$DatabaseName\`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    $processInfo = New-Object System.Diagnostics.ProcessStartInfo
    $processInfo.FileName = $mysqlPath.Source
    $processInfo.Arguments = "-h $Host -P $Port -u $AdminUser -p$AdminPassword -e `"$createDbQuery`""
    $processInfo.UseShellExecute = $false
    $processInfo.CreateNoWindow = $true
    
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $processInfo
    $process.Start() | Out-Null
    $process.WaitForExit(10000)
    
    if ($process.ExitCode -ne 0) {
        return $false
    }
    
    # Create user and grant privileges
    $createUserQuery = "CREATE USER IF NOT EXISTS '\`"$NewUser\`"'@'\`"%\`"' IDENTIFIED BY '\`"$NewPassword\`"';"
    $grantQuery = "GRANT ALL PRIVILEGES ON \`"$DatabaseName\`".* TO '\`"$NewUser\`"'@'\`"%\`"';"
    $flushQuery = "FLUSH PRIVILEGES;"
    
    $processInfo.Arguments = "-h $Host -P $Port -u $AdminUser -p$AdminPassword -e `"$createUserQuery $grantQuery $flushQuery`""
    $process.StartInfo = $processInfo
    $process.Start() | Out-Null
    $process.WaitForExit(10000)
    
    return $true
}

# ============================================
# Step 1: Check System Requirements
# ============================================

function Check-Requirements {
    Write-Header
    Write-Host "Step 1: Checking System Requirements" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────" -ForegroundColor $GRAY

    # Check PHP
    Write-Status "Checking PHP..."
    try {
        $phpVersion = php -v 2>&1 | Select-Object -First 1
        if ($phpVersion) {
            $versionMatch = $phpVersion -match '(\d+)\.(\d+)'
            if ($versionMatch) {
                $phpMajor = [int]$matches[1]
                $phpMinor = [int]$matches[2]
                $phpVersionNum = "$phpMajor.$phpMinor"

                if ($phpMajor -lt 8 -or ($phpMajor -eq 8 -and $phpMinor -lt 2)) {
                    Write-Error-Custom "PHP 8.2 or higher is required! You have $phpVersionNum"
                    Write-Host "  Please upgrade PHP to version 8.2 or higher" -ForegroundColor $YELLOW
                    Read-Host "Press Enter to exit"
                    exit 1
                }
            }
            Write-Success "PHP installed ($($phpVersion.Substring(0, [Math]::Min(20, $phpVersion.Length))))"
        } else {
            throw
        }
    } catch {
        Write-Error-Custom "PHP is not installed!"
        Write-Host "  Install from: https://windows.php.net/download/" -ForegroundColor $YELLOW
        Write-Host "  Ensure PHP is added to PATH" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Check required PHP extensions
    Write-Host ""
    Write-Host "  Checking PHP extensions..." -ForegroundColor $CYAN

    $REQUIRED_EXTENSIONS = @("mbstring", "xml", "curl", "zip", "openssl", "pdo", "json", "tokenizer", "bcmath", "mysql")
    $MISSING_EXTENSIONS = @()

    foreach ($ext in $REQUIRED_EXTENSIONS) {
        $extCheck = php -m 2>&1 | Select-String -Pattern "^$ext$" -CaseSensitive:$false
        if ($extCheck) {
            Write-Success "PHP extension: $ext"
        } else {
            Write-Error-Custom "PHP extension: $ext (MISSING)"
            $MISSING_EXTENSIONS += $ext
        }
    }

    if ($MISSING_EXTENSIONS.Count -gt 0) {
        Write-Host ""
        Write-Error-Custom "Missing PHP extensions: $($MISSING_EXTENSIONS -join ', ')"
        Write-Host "  Enable extensions in php.ini or reinstall PHP with required extensions" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Check Composer
    Write-Status "Checking Composer..."
    try {
        $composerVersion = composer --version 2>&1
        if ($composerVersion) {
            Write-Success "Composer installed"
        } else {
            throw
        }
    } catch {
        Write-Error-Custom "Composer is not installed!"
        Write-Host "  Install from: https://getcomposer.org/download/" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Check Node.js
    Write-Status "Checking Node.js..."
    try {
        $nodeVersion = node -v 2>&1
        if ($nodeVersion) {
            Write-Success "Node.js $nodeVersion installed"
        } else {
            throw
        }
    } catch {
        Write-Error-Custom "Node.js is not installed!"
        Write-Host "  Install from: https://nodejs.org/" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Check npm
    Write-Status "Checking npm..."
    try {
        $npmVersion = npm -v 2>&1
        if ($npmVersion) {
            Write-Success "npm $npmVersion installed"
        } else {
            throw
        }
    } catch {
        Write-Error-Custom "npm is not installed!"
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Check Git
    Write-Status "Checking Git..."
    try {
        $gitVersion = git --version 2>&1
        if ($gitVersion) {
            Write-Success "Git installed ($gitVersion)"
        } else {
            throw
        }
    } catch {
        Write-Error-Custom "Git is not installed!"
        Write-Host "  Install from: https://git-scm.com/download/win" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Check MySQL client (optional)
    Write-Status "Checking MySQL client..."
    if (Test-Command "mysql") {
        Write-Success "MySQL client installed"
    } else {
        Write-Warning-Custom "MySQL client not found (optional, for automatic database creation)"
        Write-Host "  Download from: https://dev.mysql.com/downloads/mysql/" -ForegroundColor $YELLOW
    }
}

# ============================================
# Step 2: Install Dependencies
# ============================================

function Install-Dependencies {
    Write-Host ""
    Write-Host "Step 2: Installing PHP Dependencies" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"
    Write-Status "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    if ($LASTEXITCODE -eq 0) {
        Write-Success "PHP dependencies installed"
    } else {
        Write-Error-Custom "Failed to install PHP dependencies"
        Read-Host "Press Enter to exit"
        exit 1
    }

    Write-Host ""
    Write-Host "Step 3: Installing JavaScript Dependencies" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"
    Write-Status "Running npm install..."
    npm install
    if ($LASTEXITCODE -eq 0) {
        Write-Success "JavaScript dependencies installed"
    } else {
        Write-Error-Custom "Failed to install JavaScript dependencies"
        Read-Host "Press Enter to exit"
        exit 1
    }
}

# ============================================
# Step 3: Setup Environment
# ============================================

function Setup-Environment {
    Write-Host ""
    Write-Host "Step 4: Setting Up Environment" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"

    # Create .env from .env.example
    if (-not (Test-Path ".env")) {
        Write-Status "Creating .env file..."
        Copy-Item .env.example .env
        Write-Success ".env file created"
    } else {
        Write-Status ".env file already exists"
        $overwrite = Read-Host "  Overwrite existing .env? (y/n) [n]"
        if ($overwrite -eq "y" -or $overwrite -eq "Y") {
            Copy-Item .env.example .env -Force
            Write-Success ".env file overwritten"
        }
    }

    # Generate application key
    Write-Status "Generating application key..."
    php artisan key:generate
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Application key generated"
    } else {
        Write-Error-Custom "Failed to generate application key"
        Read-Host "Press Enter to exit"
        exit 1
    }
}

# ============================================
# Step 4: Database Configuration
# ============================================

function Setup-SQLite {
    Write-Status "Setting up SQLite database..."

    # Update .env for SQLite
    $envContent = Get-Content .env
    $envContent = $envContent -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite'
    $envContent = $envContent -replace '^# DB_HOST=.*','# DB_HOST=127.0.0.1'
    $envContent = $envContent -replace '^# DB_PORT=.*','# DB_PORT=3306'
    $envContent = $envContent -replace '^# DB_DATABASE=.*','# DB_DATABASE=database/database.sqlite'
    $envContent = $envContent -replace '^# DB_USERNAME=.*','# DB_USERNAME=root'
    $envContent = $envContent -replace '^# DB_PASSWORD=.*','# DB_PASSWORD='
    $envContent | Set-Content .env

    # Create database directory and file
    if (-not (Test-Path "database")) {
        New-Item -ItemType Directory -Path "database" | Out-Null
    }
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
    
    Write-Success "SQLite database created at database\database.sqlite"
    $script:DB_CONNECTION = "sqlite"
}

function Setup-MySQL {
    Write-Status "Setting up MySQL database..."
    Write-Host ""

    # Get database host
    $dbHostInput = Read-Host "  Database host [127.0.0.1]"
    $script:DB_HOST = if ([string]::IsNullOrWhiteSpace($dbHostInput)) { "127.0.0.1" } else { $dbHostInput }

    # Get database port
    $dbPortInput = Read-Host "  Database port [3306]"
    $script:DB_PORT = if ([string]::IsNullOrWhiteSpace($dbPortInput)) { "3306" } else { $dbPortInput }

    # Ask if user wants to create new database or use existing
    Write-Host ""
    Write-Host "  Database Setup:" -ForegroundColor $CYAN
    Write-Host "  1) Create new database"
    Write-Host "  2) Use existing database"
    Write-Host ""
    $dbSetupChoice = Read-Host "  Enter choice [1-2]"

    if ($dbSetupChoice -eq "1") {
        # Create new database
        Write-Host ""
        $dbName = Read-Host "  New database name"
        if ([string]::IsNullOrWhiteSpace($dbName)) {
            Write-Error-Custom "Database name is required"
            Read-Host "Press Enter to exit"
            exit 1
        }

        $dbUserAdmin = Read-Host "  Database username (for creating DB) [root]"
        $dbUserAdmin = if ([string]::IsNullOrWhiteSpace($dbUserAdmin)) { "root" } else { $dbUserAdmin }

        $dbPassAdmin = Read-Host "  Database password (for admin user)" -AsSecureString
        $dbPassAdminPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
            [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassAdmin)
        )

        $dbUserNew = Read-Host "  Database user to create [same as db name]"
        $dbUserNew = if ([string]::IsNullOrWhiteSpace($dbUserNew)) { $dbName } else { $dbUserNew }

        $dbPassNew = Read-Host "  Password for new database user" -AsSecureString
        $dbPassNewPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
            [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassNew)
        )

        # Test MySQL connection
        Write-Status "Testing MySQL connection..."
        if (-not (Get-MySQLConnection -Host $script:DB_HOST -Port $script:DB_PORT -User $dbUserAdmin -Password $dbPassAdminPlain)) {
            Write-Error-Custom "Cannot connect to MySQL with provided credentials!"
            Write-Host "  Please check:" -ForegroundColor $YELLOW
            Write-Host "    1. MySQL server is running"
            Write-Host "    2. Host and port are correct"
            Write-Host "    3. Username and password are valid"
            Read-Host "Press Enter to exit"
            exit 1
        }
        Write-Success "MySQL connection successful"

        # Create database
        Write-Status "Creating database '$dbName'..."
        if (-not (Create-MySQLDatabase -Host $script:DB_HOST -Port $script:DB_PORT -AdminUser $dbUserAdmin -AdminPassword $dbPassAdminPlain -DatabaseName $dbName -NewUser $dbUserNew -NewPassword $dbPassNewPlain)) {
            Write-Error-Custom "Failed to create database"
            Read-Host "Press Enter to exit"
            exit 1
        }
        Write-Success "Database '$dbName' created"
        Write-Success "User '$dbUserNew' created and privileges granted"

        # Set variables for .env
        $script:DB_NAME = $dbName
        $script:DB_USER = $dbUserNew
        $dbPassPlain = $dbPassNewPlain

    } elseif ($dbSetupChoice -eq "2") {
        # Use existing database
        Write-Host ""
        $dbName = Read-Host "  Existing database name"
        if ([string]::IsNullOrWhiteSpace($dbName)) {
            Write-Error-Custom "Database name is required"
            Read-Host "Press Enter to exit"
            exit 1
        }

        $dbUser = Read-Host "  Database username"
        if ([string]::IsNullOrWhiteSpace($dbUser)) {
            Write-Error-Custom "Database username is required"
            Read-Host "Press Enter to exit"
            exit 1
        }

        $dbPass = Read-Host "  Database password" -AsSecureString
        $dbPassPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
            [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass)
        )

        # Test connection
        Write-Status "Testing MySQL connection..."
        if (-not (Get-MySQLConnection -Host $script:DB_HOST -Port $script:DB_PORT -User $dbUser -Password $dbPassPlain -Database $dbName)) {
            Write-Error-Custom "Cannot connect to MySQL database!"
            Write-Host "  Please check:" -ForegroundColor $YELLOW
            Write-Host "    1. MySQL server is running"
            Write-Host "    2. Database '$dbName' exists"
            Write-Host "    3. Username and password are correct"
            Write-Host "    4. User has privileges on the database"
            Read-Host "Press Enter to exit"
            exit 1
        }
        Write-Success "MySQL connection successful"

        # Set variables
        $script:DB_NAME = $dbName
        $script:DB_USER = $dbUser
        $dbPassPlain = $dbPassPlain
    } else {
        Write-Error-Custom "Invalid choice. Please run the script again."
        Read-Host "Press Enter to exit"
        exit 1
    }

    # Update .env for MySQL
    $envContent = Get-Content .env
    $envContent = $envContent -replace '^DB_CONNECTION=.*','DB_CONNECTION=mysql'
    $envContent = $envContent -replace '^# DB_HOST=.*',"DB_HOST=$script:DB_HOST"
    $envContent = $envContent -replace '^# DB_PORT=.*',"DB_PORT=$script:DB_PORT"
    $envContent = $envContent -replace '^# DB_DATABASE=.*',"DB_DATABASE=$script:DB_NAME"
    $envContent = $envContent -replace '^# DB_USERNAME=.*',"DB_USERNAME=$script:DB_USER"
    $envContent = $envContent -replace '^# DB_PASSWORD=.*',"DB_PASSWORD=$dbPassPlain"
    $envContent | Set-Content .env

    # Clear config cache
    php artisan config:clear | Out-Null

    Write-Success "MySQL configuration saved to .env"
    $script:DB_CONNECTION = "mysql"
}

function Configure-Database {
    Write-Host ""
    Write-Host "Step 5: Database Configuration" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"
    Write-Host ""
    Write-Host "  Select database type:" -ForegroundColor $CYAN
    Write-Host "  1) SQLite (recommended for development/testing)"
    Write-Host "  2) MySQL/MariaDB (recommended for production)"
    Write-Host ""
    $dbChoice = Read-Host "  Enter choice [1-2]"

    if ($dbChoice -eq "1") {
        Setup-SQLite
    } elseif ($dbChoice -eq "2") {
        Setup-MySQL
    } else {
        Write-Error-Custom "Invalid choice. Defaulting to SQLite."
        Setup-SQLite
    }
}

# ============================================
# Step 5: Run Migrations
# ============================================

function Run-Migrations {
    Write-Host ""
    Write-Host "Step 6: Running Database Migrations" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"

    Write-Status "Clearing configuration cache..."
    php artisan config:clear | Out-Null
    php artisan cache:clear | Out-Null

    Write-Status "Running migrations..."
    php artisan migrate --force
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Database migrations completed successfully"
    } else {
        Write-Error-Custom "Failed to run migrations"
        Write-Host ""
        Write-Host "  Troubleshooting:" -ForegroundColor $YELLOW
        Write-Host "    1. Check database credentials in .env"
        Write-Host "    2. Ensure database exists and is accessible"
        Write-Host "    3. Check storage/logs/laravel.log for errors"
        Write-Host "    4. Run 'php artisan migrate:status' to see migration status"
        Write-Host ""
        Read-Host "Press Enter to exit"
        exit 1
    }
}

# ============================================
# Step 6: Create Admin User
# ============================================

function Create-AdminUser {
    Write-Host ""
    Write-Host "Step 7: Creating Admin User" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"

    # Check if AdminUserSeeder exists
    if (Test-Path "database/seeders/AdminUserSeeder.php") {
        Write-Status "Running AdminUserSeeder..."
        php artisan db:seed --class=AdminUserSeeder --force
        Write-Success "Admin user created"
    } else {
        Write-Status "Creating admin user directly..."

        # Create admin user using tinker
        $tinkerCode = @'
$user = \App\Models\User::where('email', 'admin@example.com')->first();
if (!$user) {
    $user = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'email_verified_at' => now(),
        'is_admin' => true,
        'username' => 'admin',
    ]);
    \App\Models\Profile::create(['user_id' => $user->id]);
    echo "Admin user created successfully\n";
} else {
    echo "Admin user already exists\n";
}
'@
        php artisan tinker --execute="$tinkerCode"
        Write-Success "Admin user setup complete"
    }
}

# ============================================
# Step 7: Build Frontend
# ============================================

function Build-Frontend {
    Write-Host ""
    Write-Host "Step 8: Building Frontend Assets" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"
    Write-Status "Building assets with Vite..."

    npm run build
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Frontend assets built successfully"
    } else {
        Write-Error-Custom "Failed to build frontend assets"
        Write-Host "  Try running 'npm install' again and check for errors" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }
}

# ============================================
# Step 8: Storage and Permissions
# ============================================

function Setup-Storage {
    Write-Host ""
    Write-Host "Step 9: Setting Up Storage" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"

    # Create storage link
    Write-Status "Creating storage symbolic link..."
    php artisan storage:link 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Storage link created"
    } else {
        Write-Status "Storage link may already exist"
    }

    # Set permissions (Windows doesn't use chmod the same way)
    Write-Status "Setting storage permissions..."
    if (Test-Path "storage") {
        # On Windows, we just ensure the directories exist
        Write-Success "Storage directories verified"
    }

    if (Test-Path "bootstrap/cache") {
        Write-Success "Cache directory verified"
    }
}

# ============================================
# Step 9: Final Setup
# ============================================

function Finalize-Setup {
    Write-Host ""
    Write-Host "Step 10: Finalizing Setup" -ForegroundColor $WHITE -BackgroundColor Black
    Write-Host "────────────────────────────────────────"

    Write-Status "Clearing all caches..."
    php artisan config:clear | Out-Null
    php artisan cache:clear | Out-Null
    php artisan view:clear | Out-Null
    php artisan route:clear | Out-Null
    php artisan event:clear | Out-Null
    Write-Success "All caches cleared"

    # Optimize (optional)
    Write-Host ""
    $optimize = Read-Host "  Optimize for production? (y/n) [n]"
    if ($optimize -eq "y" -or $optimize -eq "Y") {
        Write-Status "Optimizing application..."
        php artisan optimize
        Write-Success "Application optimized"
    }
}

# ============================================
# Summary
# ============================================

function Print-Summary {
    Write-Host ""
    Write-Host "════════════════════════════════════════════════════" -ForegroundColor $GREEN
    Write-Host "  ✓ Setup Complete!" -ForegroundColor $GREEN
    Write-Host "════════════════════════════════════════════════════" -ForegroundColor $GREEN
    Write-Host ""
    Write-Host "  Your Nexus project is ready to use!" -ForegroundColor $GREEN
    Write-Host ""
    Write-Host "────────────────────────────────────────────────────────" -ForegroundColor $GRAY
    Write-Host "  Database Configuration:" -ForegroundColor $CYAN
    Write-Host "    Type: $script:DB_CONNECTION"
    if ($script:DB_CONNECTION -eq "mysql") {
        Write-Host "    Host: $script:DB_HOST`:$script:DB_PORT"
        Write-Host "    Database: $script:DB_NAME"
        Write-Host "    Username: $script:DB_USER"
    }
    Write-Host ""
    Write-Host "  Admin Login Credentials:" -ForegroundColor $CYAN
    Write-Host "    URL:      http://localhost:8000"
    Write-Host "    Email:    admin@example.com"
    Write-Host "    Password: admin123"
    Write-Host "    Username: admin"
    Write-Host ""
    Write-Host "  ⚠ Security Notice: Change the default password after login!" -ForegroundColor $YELLOW
    Write-Host "────────────────────────────────────────────────────────" -ForegroundColor $GRAY
    Write-Host ""
    Write-Host "  To start the development server:" -ForegroundColor $WHITE
    Write-Host "    php artisan serve" -ForegroundColor $CYAN
    Write-Host ""
    Write-Host "  To start with development mode (server + queue + vite):" -ForegroundColor $WHITE
    Write-Host "    composer run dev" -ForegroundColor $CYAN
    Write-Host ""
    Write-Host "  Useful Commands:" -ForegroundColor $WHITE
    Write-Host "    php artisan migrate          - Run migrations"
    Write-Host "    php artisan migrate:fresh    - Reset and run migrations"
    Write-Host "    php artisan db:seed          - Seed database"
    Write-Host "    npm run dev                  - Start Vite dev server"
    Write-Host "    php artisan optimize         - Optimize for production"
    Write-Host ""
    Write-Host "  Enjoy building with Nexus! 🚀" -ForegroundColor $GREEN
    Write-Host ""
    Read-Host "Press Enter to exit"
}

# ============================================
# Main Execution
# ============================================

function Main {
    try {
        Check-Requirements
        Install-Dependencies
        Setup-Environment
        Configure-Database
        Run-Migrations
        Create-AdminUser
        Build-Frontend
        Setup-Storage
        Finalize-Setup
        Print-Summary
    } catch {
        Write-Error-Custom "Setup failed: $_"
        Write-Host "  Check the error messages above for details" -ForegroundColor $YELLOW
        Read-Host "Press Enter to exit"
        exit 1
    }
}

# Run main function
Main
