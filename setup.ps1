# ============================================
# Nexus - Project Setup Script (PowerShell)
# For new users who cloned the project
# ============================================

# Colors
$GREEN = [ConsoleColor]::Green
$RED = [ConsoleColor]::Red
$YELLOW = [ConsoleColor]::Yellow
$CYAN = [ConsoleColor]::Cyan
$WHITE = [ConsoleColor]::White

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

# Check if running in project directory
if (-not (Test-Path "composer.json")) {
    Write-Error-Custom "Error: composer.json not found!"
    Write-Error-Custom "Please run this script from the project root directory."
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════╗" -ForegroundColor $CYAN
Write-Host "║     Nexus - Setup Script (PowerShell)              ║" -ForegroundColor $CYAN
Write-Host "╚════════════════════════════════════════════════════╝" -ForegroundColor $CYAN
Write-Host ""

Write-Host "Step 1: Checking System Requirements" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────" -ForegroundColor $GRAY

# Check PHP
Write-Status "Checking PHP..."
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    if ($phpVersion) {
        # Extract version number
        $versionMatch = $phpVersion -match '(\d+)\.(\d+)'
        if ($versionMatch) {
            $phpMajor = $matches[1]
            $phpMinor = $matches[2]
            $phpVersionNum = "$phpMajor.$phpMinor"
            
            # Check minimum PHP version (8.2 required)
            if ([int]$phpMajor -lt 8 -or ([int]$phpMajor -eq 8 -and [int]$phpMinor -lt 2)) {
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
    Read-Host "Press Enter to exit"
    exit 1
}

# Check required PHP extensions
Write-Host ""
Write-Host "  Checking PHP extensions..." -ForegroundColor $CYAN

$REQUIRED_EXTENSIONS = @("mbstring", "xml", "curl", "zip", "openssl", "pdo", "json", "tokenizer")
$MISSING_EXTENSIONS = @()

foreach ($ext in $REQUIRED_EXTENSIONS) {
    $extCheck = php -m 2>&1 | Select-String -Pattern $ext -CaseSensitive:$false
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
    Write-Host "  Enable extensions in php.ini or install: php-mbstring php-xml php-curl php-zip php-sqlite3 php-mysql" -ForegroundColor $YELLOW
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

# Check required system tools
Write-Status "Checking system tools..."
if (Get-Command unzip -ErrorAction SilentlyContinue) {
    Write-Success "unzip installed"
} else {
    Write-Error-Custom "unzip is not installed!"
    Write-Host "  Install from: https://info-zip.org/" -ForegroundColor $YELLOW
    Read-Host "Press Enter to exit"
    exit 1
}

if (Get-Command jq -ErrorAction SilentlyContinue) {
    Write-Success "jq installed (for tunnel logs)"
} else {
    Write-Status "jq not found (optional, for tunnel visitor logs)"
    Write-Host "  Install from: https://jqlang.github.io/jq/download/ (recommended for tunnel mode)" -ForegroundColor $YELLOW
}

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

Write-Host ""
Write-Host "Step 5: Setting Up Database" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────"
Write-Host ""
Write-Host "  Select database type:"
Write-Host "  1) SQLite (recommended for development)"
Write-Host "  2) MySQL/MariaDB"
Write-Host ""
$dbChoice = Read-Host "  Enter choice [1-2]"

if ($dbChoice -eq "1") {
    Write-Status "Setting up SQLite database..."
    
    # Update .env for SQLite
    (Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' | Set-Content .env
    
    # Create database directory if not exists
    if (-not (Test-Path "database")) {
        New-Item -ItemType Directory -Path "database" | Out-Null
    }
    
    # Create SQLite database file
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
    Write-Success "SQLite database created"
    
} elseif ($dbChoice -eq "2") {
    Write-Status "Setting up MySQL database..."
    Write-Host ""
    $dbName = Read-Host "  Database name [laravel]"
    if ([string]::IsNullOrWhiteSpace($dbName)) { $dbName = "laravel" }
    
    $dbUser = Read-Host "  Database username [root]"
    if ([string]::IsNullOrWhiteSpace($dbUser)) { $dbUser = "root" }
    
    $dbPass = Read-Host "  Database password" -AsSecureString
    $dbPassPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass)
    )
    
    # Update .env for MySQL
    (Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=mysql' | Set-Content .env
    (Get-Content .env) -replace '^# DB_HOST=.*','DB_HOST=127.0.0.1' | Set-Content .env
    (Get-Content .env) -replace '^# DB_PORT=.*','DB_PORT=3306' | Set-Content .env
    (Get-Content .env) -replace '^# DB_DATABASE=.*',"DB_DATABASE=$dbName" | Set-Content .env
    (Get-Content .env) -replace '^# DB_USERNAME=.*',"DB_USERNAME=$dbUser" | Set-Content .env
    (Get-Content .env) -replace '^# DB_PASSWORD=.*',"DB_PASSWORD=$dbPassPlain" | Set-Content .env
    
    Write-Success "MySQL configuration saved"
    Write-Status "Please create the database '$dbName' in MySQL before running migrations"
    Write-Host "  CREATE DATABASE $dbName;" -ForegroundColor $YELLOW
    
} else {
    Write-Error-Custom "Invalid choice. Using SQLite by default."
    if (-not (Test-Path "database")) {
        New-Item -ItemType Directory -Path "database" | Out-Null
    }
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
    (Get-Content .env) -replace '^DB_CONNECTION=.*','DB_CONNECTION=sqlite' | Set-Content .env
}

Write-Host ""
Write-Host "Step 6: Running Database Migrations" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────"
Write-Status "Running migrations..."
php artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Success "Database migrations completed"
} else {
    Write-Error-Custom "Failed to run migrations"
    Write-Host "  Make sure database is properly configured" -ForegroundColor $YELLOW
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Step 7: Seeding Admin User" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────"
Write-Status "Running admin user seeder..."
php artisan db:seed --class=AdminUserSeeder --force
if ($LASTEXITCODE -eq 0) {
    Write-Success "Admin user created"
} else {
    Write-Status "Admin user may already exist"
}

Write-Host ""
Write-Host "Step 8: Building Frontend Assets" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────"
Write-Status "Building assets with npm..."
npm run build
if ($LASTEXITCODE -eq 0) {
    Write-Success "Frontend assets built"
} else {
    Write-Error-Custom "Failed to build assets"
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Step 9: Creating Storage Links" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────"
Write-Status "Creating storage link..."
php artisan storage:link
if ($LASTEXITCODE -eq 0) {
    Write-Success "Storage link created"
} else {
    Write-Status "Storage link may already exist"
}

Write-Host ""
Write-Host "Step 10: Clearing Caches" -ForegroundColor $WHITE -BackgroundColor Black
Write-Host "────────────────────────────────────────"
Write-Status "Clearing caches..."
php artisan config:clear | Out-Null
php artisan cache:clear | Out-Null
php artisan view:clear | Out-Null
php artisan route:clear | Out-Null
Write-Success "Caches cleared"

Write-Host ""
Write-Host "════════════════════════════════════════" -ForegroundColor $GREEN
Write-Host "  Setup Complete!" -ForegroundColor $GREEN
Write-Host "════════════════════════════════════════" -ForegroundColor $GREEN
Write-Host ""
Write-Host "Your Nexus project is ready!" -ForegroundColor $GREEN
Write-Host ""
Write-Host "Admin Login Credentials:" -ForegroundColor $CYAN
Write-Host "  Email:    admin@example.com"
Write-Host "  Password: admin123"
Write-Host "  Username: admin"
Write-Host ""
Write-Host "To start the development server:" -ForegroundColor $CYAN
Write-Host "  php artisan serve"
Write-Host ""
Write-Host "Or to start with tunnel (for public URL):" -ForegroundColor $CYAN
Write-Host "  .\start-tunnel.sh (requires Git Bash)"
Write-Host ""
Write-Host "Enjoy! 🚀" -ForegroundColor $GREEN
Write-Host ""
Read-Host "Press Enter to exit"
