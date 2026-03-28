#!/bin/bash

# ============================================
# Nexus - Complete Project Setup Script
# For Linux/macOS - Full MySQL Support
# ============================================

set -e  # Exit on error

# Global variables
DB_CONNECTION=""
DB_NAME=""
DB_USER=""
DB_PASS=""
DB_HOST="127.0.0.1"
DB_PORT="3306"

# ============================================
# Helper Functions
# ============================================

print_header() {
    echo ""
    echo "Nexus - Complete Setup Script"
    echo ""
}

print_status() {
    echo "  ● $1"
}

print_success() {
    echo "  ✓ $1"
}

print_error() {
    echo "  ✗ $1"
}

print_warning() {
    echo "  ⚠ $1"
}

print_info() {
    echo "  ℹ $1"
}

cleanup_and_exit() {
    local exit_code=$1
    if [ $exit_code -ne 0 ]; then
        echo ""
        print_error "Setup failed! Please check the errors above."
        echo "  Troubleshooting tips:"
        echo "    1. Check if all requirements are installed"
        echo "    2. Verify database credentials are correct"
        echo "    3. Ensure PHP extensions are enabled"
        echo "    4. Check storage/logs/laravel.log for errors"
        echo ""
    fi
    exit $exit_code
}

# Trap errors
trap 'cleanup_and_exit $?' ERR

# ============================================
# Step 1: Check System Requirements
# ============================================

check_requirements() {
    print_header
    echo "Step 1: Checking System Requirements"
    echo "────────────────────────────────────────"

    # Check PHP
    print_status "Checking PHP..."
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n 1 | cut -d' ' -f2 | cut -d'.' -f1,2)
        PHP_MAJOR=$(php -v | head -n 1 | cut -d' ' -f2 | cut -d'.' -f1)
        PHP_MINOR=$(php -v | head -n 1 | cut -d' ' -f2 | cut -d'.' -f2)
        print_success "PHP $PHP_VERSION installed"

        if [ "$PHP_MAJOR" -lt 8 ] || { [ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]; }; then
            print_error "PHP 8.2 or higher is required! You have $PHP_VERSION"
            echo -n "  Would you like to install PHP 8.3? (y/n) [n]: "
            read -r INSTALL_PHP
            if [ "$INSTALL_PHP" = "y" ] || [ "$INSTALL_PHP" = "Y" ]; then
                print_status "Installing PHP 8.3..."
                if command -v apt &> /dev/null; then
                    sudo add-apt-repository ppa:ondrej/php -y
                    sudo apt update
                    sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-sqlite3 php8.3-mysql php8.3-bcmath php8.3-gd -y
                    print_success "PHP 8.3 installed"
                elif command -v brew &> /dev/null; then
                    brew install php@8.3
                    print_success "PHP 8.3 installed"
                else
                    print_error "Automatic installation not supported for your system"
                    echo "  Please install manually: https://www.php.net/manual/en/install.php"
                    exit 1
                fi
            else
                exit 1
            fi
        fi
    else
        print_error "PHP is not installed!"
        echo -n "  Would you like to install PHP? (y/n) [n]: "
        read -r INSTALL_PHP
        if [ "$INSTALL_PHP" = "y" ] || [ "$INSTALL_PHP" = "Y" ]; then
            print_status "Installing PHP..."
            if command -v apt &> /dev/null; then
                sudo apt update
                sudo apt install php php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 php-mysql php-bcmath php-gd -y
                print_success "PHP installed"
            elif command -v brew &> /dev/null; then
                brew install php
                print_success "PHP installed"
            else
                print_error "Automatic installation not supported for your system"
                echo "  Please install manually: https://www.php.net/manual/en/install.php"
                exit 1
            fi
        else
            echo "  Install with: sudo apt install php php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 php-mysql php-bcmath"
            exit 1
        fi
    fi

    # Check required PHP extensions
    echo ""
    echo "  Checking PHP extensions..."
    REQUIRED_EXTENSIONS=("mbstring" "xml" "curl" "zip" "openssl" "pdo" "json" "tokenizer" "bcmath" "mysql")
    MISSING_EXTENSIONS=()

    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -qi "$ext"; then
            print_success "PHP extension: $ext"
        else
            print_error "PHP extension: $ext (MISSING)"
            MISSING_EXTENSIONS+=("$ext")
        fi
    done

    if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
        echo ""
        print_error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
        echo "  Install with: sudo apt install php-mbstring php-xml php-curl php-zip php-mysql php-bcmath"
        exit 1
    fi

    # Check Composer
    print_status "Checking Composer..."
    if command -v composer &> /dev/null; then
        COMPOSER_VERSION=$(composer --version | cut -d' ' -f3)
        print_success "Composer $COMPOSER_VERSION installed"
    else
        print_error "Composer is not installed!"
        echo -n "  Would you like to install Composer? (y/n) [n]: "
        read -r INSTALL_COMPOSER
        if [ "$INSTALL_COMPOSER" = "y" ] || [ "$INSTALL_COMPOSER" = "Y" ]; then
            print_status "Installing Composer..."
            curl -sS https://getcomposer.org/installer | php
            sudo mv composer.phar /usr/local/bin/composer
            print_success "Composer installed"
        else
            echo "  Install with: curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer"
            exit 1
        fi
    fi
    print_status "Checking Node.js..."
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node -v)
        print_success "Node.js $NODE_VERSION installed"
    else
        print_error "Node.js is not installed!"
        echo -n "  Would you like to install Node.js LTS? (y/n) [n]: "
        read -r INSTALL_NODE
        if [ "$INSTALL_NODE" = "y" ] || [ "$INSTALL_NODE" = "Y" ]; then
            print_status "Installing Node.js LTS..."
            if command -v apt &> /dev/null; then
                curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
                sudo apt install -y nodejs
                print_success "Node.js LTS installed"
            elif command -v brew &> /dev/null; then
                brew install node@20
                print_success "Node.js LTS installed"
            else
                print_error "Automatic installation not supported for your system"
                echo "  Please install manually: https://nodejs.org/"
                exit 1
            fi
        else
            echo "  Install with: curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - && sudo apt install -y nodejs"
            exit 1
        fi
    fi

    # Check npm
    print_status "Checking npm..."
    if command -v npm &> /dev/null; then
        NPM_VERSION=$(npm -v)
        print_success "npm $NPM_VERSION installed"
    else
        print_error "npm is not installed!"
        exit 1
    fi

    # Check Git
    print_status "Checking Git..."
    if command -v git &> /dev/null; then
        GIT_VERSION=$(git --version | cut -d' ' -f3)
        print_success "Git $GIT_VERSION installed"
    else
        print_error "Git is not installed!"
        exit 1
    fi

    # Check unzip
    print_status "Checking system tools..."
    if command -v unzip &> /dev/null; then
        print_success "unzip installed"
    else
        print_error "unzip is not installed!"
        echo "  Install with: sudo apt install unzip"
        exit 1
    fi

    # Check MySQL client (for database creation)
    if command -v mysql &> /dev/null; then
        print_success "MySQL client installed"
    else
        print_warning "MySQL client not found (optional, for database creation)"
    fi
}

# ============================================
# Step 2: Install Dependencies
# ============================================

install_dependencies() {
    echo ""
    echo "Step 2: Installing PHP Dependencies"
    echo "────────────────────────────────────────"
    print_status "Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "PHP dependencies installed"

    echo ""
    echo "Step 3: Installing JavaScript Dependencies"
    echo "────────────────────────────────────────"
    print_status "Running npm install..."
    npm install
    print_success "JavaScript dependencies installed"
}

# ============================================
# Step 4: Setup Environment
# ============================================

setup_environment() {
    echo ""
    echo "Step 4: Setting Up Environment"
    echo "────────────────────────────────────────"

    # Create .env from .env.example
    if [ ! -f ".env" ]; then
        print_status "Creating .env file..."
        cp .env.example .env
        print_success ".env file created"
    else
        print_status ".env file already exists"
        echo -n "  Overwrite existing .env? (y/n) [n]: "
        read -r OVERWRITE
        if [ "$OVERWRITE" = "y" ] || [ "$OVERWRITE" = "Y" ]; then
            cp .env.example .env
            print_success ".env file overwritten"
        fi
    fi

    # Generate application key
    print_status "Generating application key..."
    php artisan key:generate
    print_success "Application key generated"
}

# ============================================
# Step 5: Database Configuration
# ============================================

configure_database() {
    echo ""
    echo "Step 5: Database Configuration"
    echo "────────────────────────────────────────"
    echo ""
    echo "  Select database type:"
    echo "  1) SQLite (recommended for development/testing)"
    echo "  2) MySQL/MariaDB (recommended for production)"
    echo ""
    echo -n "  Enter choice [1-2]: "
    read -r DB_CHOICE

    if [ "$DB_CHOICE" = "1" ]; then
        setup_sqlite
    elif [ "$DB_CHOICE" = "2" ]; then
        setup_mysql
    else
        print_error "Invalid choice. Defaulting to SQLite."
        setup_sqlite
    fi
}

setup_sqlite() {
    print_status "Setting up SQLite database..."

    # Update .env for SQLite
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i 's/^# DB_HOST=.*/# DB_HOST=127.0.0.1/' .env
    sed -i 's/^# DB_PORT=.*/# DB_PORT=3306/' .env
    sed -i 's/^# DB_DATABASE=.*/# DB_DATABASE=database/database.sqlite/' .env
    sed -i 's/^# DB_USERNAME=.*/# DB_USERNAME=root/' .env
    sed -i 's/^# DB_PASSWORD=.*/# DB_PASSWORD=/' .env

    # Create database directory and file
    mkdir -p database
    touch database/database.sqlite
    chmod 666 database/database.sqlite

    print_success "SQLite database created at database/database.sqlite"
    DB_CONNECTION="sqlite"
}

setup_mysql() {
    print_status "Setting up MySQL database..."
    echo ""

    # Get database host
    echo -ne "  Database host [127.0.0.1]: "
    read -r DB_HOST_INPUT
    DB_HOST=${DB_HOST_INPUT:-127.0.0.1}

    # Get database port
    echo -ne "  Database port [3306]: "
    read -r DB_PORT_INPUT
    DB_PORT=${DB_PORT_INPUT:-3306}

    # Ask if user wants to create new database or use existing
    echo ""
    echo "  Database Setup:"
    echo "  1) Create new database"
    echo "  2) Use existing database"
    echo ""
    echo -ne "  Enter choice [1-2]: "
    read -r DB_SETUP_CHOICE

    if [ "$DB_SETUP_CHOICE" = "1" ]; then
        # Create new database
        echo -ne "  New database name: "
        read -r DB_NAME

        echo -ne "  Database username (for creating DB): "
        read -r DB_USER_ADMIN
        DB_USER_ADMIN=${DB_USER_ADMIN:-root}

        echo -ne "  Database password (for admin user): "
        read -s DB_PASS_ADMIN
        echo ""

        echo -ne "  Database user to create [same as db name]: "
        read -r DB_USER_NEW
        DB_USER_NEW=${DB_USER_NEW:-$DB_NAME}

        echo -ne "  Password for new database user: "
        read -s DB_PASS_NEW
        echo ""

        # Test MySQL connection
        print_status "Testing MySQL connection..."
        if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER_ADMIN" -p"$DB_PASS_ADMIN" -e "SELECT 1;" &> /dev/null; then
            print_error "Cannot connect to MySQL with provided credentials!"
            echo "  Please check:"
            echo "    1. MySQL server is running"
            echo "    2. Host and port are correct"
            echo "    3. Username and password are valid"
            exit 1
        fi
        print_success "MySQL connection successful"

        # Create database
        print_status "Creating database '$DB_NAME'..."
        if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER_ADMIN" -p"$DB_PASS_ADMIN" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1; then
            print_success "Database '$DB_NAME' created"
        else
            print_error "Failed to create database"
            exit 1
        fi

        # Create user and grant privileges
        print_status "Creating user '$DB_USER_NEW' and granting privileges..."
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER_ADMIN" -p"$DB_PASS_ADMIN" -e "CREATE USER IF NOT EXISTS '\`${DB_USER_NEW}\`'@'\`%\`' IDENTIFIED BY '\`${DB_PASS_NEW}\`';" 2>&1 || true
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER_ADMIN" -p"$DB_PASS_ADMIN" -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '\`${DB_USER_NEW}\`'@'\`%\`';" 2>&1 || true
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER_ADMIN" -p"$DB_PASS_ADMIN" -e "FLUSH PRIVILEGES;" 2>&1 || true
        print_success "User created and privileges granted"

        # Set variables for .env
        DB_USER=$DB_USER_NEW
        DB_PASS=$DB_PASS_NEW

    elif [ "$DB_SETUP_CHOICE" = "2" ]; then
        # Use existing database
        echo -ne "  Existing database name: "
        read -r DB_NAME

        echo -ne "  Database username: "
        read -r DB_USER

        echo -ne "  Database password: "
        read -s DB_PASS
        echo ""

        # Test connection
        print_status "Testing MySQL connection..."
        if ! mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "USE \`${DB_NAME}\`;" 2>&1; then
            print_error "Cannot connect to MySQL database!"
            echo "  Please check:"
            echo "    1. MySQL server is running"
            echo "    2. Database '$DB_NAME' exists"
            echo "    3. Username and password are correct"
            echo "    4. User has privileges on the database"
            exit 1
        fi
        print_success "MySQL connection successful"
    else
        print_error "Invalid choice. Please run the script again."
        exit 1
    fi

    # Update .env for MySQL
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i "s/^# DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/^# DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i "s/^# DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/^# DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/^# DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

    # Clear config cache to ensure new settings are used
    php artisan config:clear

    print_success "MySQL configuration saved to .env"
    DB_CONNECTION="mysql"
}

# ============================================
# Step 6: Run Migrations
# ============================================

run_migrations() {
    echo ""
    echo "Step 6: Running Database Migrations"
    echo "────────────────────────────────────────"

    print_status "Clearing configuration cache..."
    php artisan config:clear
    php artisan cache:clear

    print_status "Running migrations..."
    if php artisan migrate --force; then
        print_success "Database migrations completed successfully"
    else
        print_error "Failed to run migrations"
        echo ""
        echo "  Troubleshooting:"
        echo "    1. Check database credentials in .env"
        echo "    2. Ensure database exists and is accessible"
        echo "    3. Check storage/logs/laravel.log for errors"
        echo "    4. Run 'php artisan migrate:status' to see migration status"
        echo ""
        exit 1
    fi
}

# ============================================
# Step 7: Create Admin User
# ============================================

create_admin_user() {
    echo ""
    echo "Step 7: Creating Admin User"
    echo "────────────────────────────────────────"

    # Check if AdminUserSeeder exists
    if [ -f "database/seeders/AdminUserSeeder.php" ]; then
        print_status "Running AdminUserSeeder..."
        php artisan db:seed --class=AdminUserSeeder --force
        print_success "Admin user created"
    else
        print_status "Creating admin user directly..."

        # Create admin user using tinker
        php artisan tinker --execute="
            \$user = \App\Models\User::where('email', 'admin@example.com')->first();
            if (!\$user) {
                \$user = \App\Models\User::create([
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('admin123'),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                    'username' => 'admin',
                ]);
                \App\Models\Profile::create(['user_id' => \$user->id]);
                echo 'Admin user created successfully';
            } else {
                echo 'Admin user already exists';
            }
        "
        print_success "Admin user setup complete"
    fi
}

# ============================================
# Step 8: Build Frontend
# ============================================

build_frontend() {
    echo ""
    echo "Step 8: Building Frontend Assets"
    echo "────────────────────────────────────────"
    print_status "Building assets with Vite..."

    if npm run build; then
        print_success "Frontend assets built successfully"
    else
        print_error "Failed to build frontend assets"
        echo "  Try running 'npm install' again and check for errors"
        exit 1
    fi
}

# ============================================
# Step 9: Storage and Permissions
# ============================================

setup_storage() {
    echo ""
    echo "Step 9: Setting Up Storage"
    echo "────────────────────────────────────────"

    # Create storage link
    print_status "Creating storage symbolic link..."
    php artisan storage:link 2>/dev/null && print_success "Storage link created" || print_status "Storage link may already exist"

    # Set permissions
    print_status "Setting storage permissions..."
    if [ -d "storage" ]; then
        chmod -R 775 storage/
        chown -R "$USER":$(whoami) storage/ 2>/dev/null || true
        print_success "Storage permissions set"
    fi

    if [ -d "bootstrap/cache" ]; then
        chmod -R 775 bootstrap/cache/
        print_success "Cache directory permissions set"
    fi
}

# ============================================
# Step 10: Final Setup
# ============================================

finalize_setup() {
    echo ""
    echo "Step 10: Finalizing Setup"
    echo "────────────────────────────────────────"

    print_status "Clearing all caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    php artisan event:clear
    print_success "All caches cleared"

    # Optimize (optional, for production)
    echo ""
    echo -ne "  Optimize for production? (y/n) [n]: "
    read -r OPTIMIZE
    if [ "$OPTIMIZE" = "y" ] || [ "$OPTIMIZE" = "Y" ]; then
        print_status "Optimizing application..."
        php artisan optimize
        print_success "Application optimized"
    fi
}

# ============================================
# Summary
# ============================================

print_summary() {
    echo ""
    echo "Setup Complete!"
    echo ""
    echo "Your Nexus project is ready to use!"
    echo ""
    echo "Database Configuration:"
    echo "  Type: $DB_CONNECTION"
    if [ "$DB_CONNECTION" = "mysql" ]; then
        echo "  Host: $DB_HOST:$DB_PORT"
        echo "  Database: $DB_NAME"
        echo "  Username: $DB_USER"
    fi
    echo ""
    echo "Admin Login Credentials:"
    echo "  URL:      http://localhost:8000"
    echo "  Email:    admin@example.com"
    echo "  Password: admin123"
    echo "  Username: admin"
    echo ""
    echo "Security Notice: Change the default password after login!"
    echo ""
    echo "To start the development server:"
    echo "  php artisan serve"
    echo ""
    echo "To start with development mode (server + queue + vite):"
    echo "  composer run dev"
    echo ""
    echo "To share via public tunnel:"
    echo "  ./start-tunnel.sh"
    echo ""
    echo "Useful Commands:"
    echo "  php artisan migrate          - Run migrations"
    echo "  php artisan migrate:fresh    - Reset and run migrations"
    echo "  php artisan db:seed          - Seed database"
    echo "  npm run dev                  - Start Vite dev server"
    echo "  php artisan optimize         - Optimize for production"
    echo ""
    echo "Enjoy building with Nexus!"
    echo ""
}

# ============================================
# Main Execution
# ============================================

main() {
    check_requirements
    install_dependencies
    setup_environment
    configure_database
    run_migrations
    create_admin_user
    build_frontend
    setup_storage
    finalize_setup
    print_summary
}

# Run main function
main
