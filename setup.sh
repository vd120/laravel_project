#!/bin/bash

# ============================================
# Nexus - Project Setup Script
# For new users who cloned the project
# ============================================

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

echo ""
echo -e "${CYAN}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║${NC}     ${BOLD}Nexus - Setup Script${NC}                      ${CYAN}║${NC}"
echo -e "${CYAN}╚════════════════════════════════════════════════════╝${NC}"
echo ""

# Function to print status
print_status() {
    echo -e "  ${CYAN}●${NC} $1"
}

print_success() {
    echo -e "  ${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "  ${RED}✗${NC} $1"
}

# Check if running in project directory
if [ ! -f "composer.json" ]; then
    print_error "Error: composer.json not found!"
    print_error "Please run this script from the project root directory."
    exit 1
fi

echo -e "${BOLD}Step 1: Checking System Requirements${NC}"
echo "────────────────────────────────────────"

# Check PHP
print_status "Checking PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    print_success "PHP $PHP_VERSION installed"
else
    print_error "PHP is not installed!"
    echo -e "  ${YELLOW}Install with:${NC} sudo apt install php php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 php-mysql"
    exit 1
fi

# Check Composer
print_status "Checking Composer..."
if command -v composer &> /dev/null; then
    print_success "Composer installed"
else
    print_error "Composer is not installed!"
    echo -e "  ${YELLOW}Install with:${NC} curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer"
    exit 1
fi

# Check Node.js
print_status "Checking Node.js..."
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    print_success "Node.js $NODE_VERSION installed"
else
    print_error "Node.js is not installed!"
    echo -e "  ${YELLOW}Install with:${NC} curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - && sudo apt install -y nodejs"
    exit 1
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
    print_success "Git installed"
else
    print_error "Git is not installed!"
    exit 1
fi

echo ""
echo -e "${BOLD}Step 2: Installing PHP Dependencies${NC}"
echo "────────────────────────────────────────"
print_status "Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader
if [ $? -eq 0 ]; then
    print_success "PHP dependencies installed"
else
    print_error "Failed to install PHP dependencies"
    exit 1
fi

echo ""
echo -e "${BOLD}Step 3: Installing JavaScript Dependencies${NC}"
echo "────────────────────────────────────────"
print_status "Running npm install..."
npm install
if [ $? -eq 0 ]; then
    print_success "JavaScript dependencies installed"
else
    print_error "Failed to install JavaScript dependencies"
    exit 1
fi

echo ""
echo -e "${BOLD}Step 4: Setting Up Environment${NC}"
echo "────────────────────────────────────────"

# Create .env from .env.example
if [ ! -f ".env" ]; then
    print_status "Creating .env file..."
    cp .env.example .env
    print_success ".env file created"
else
    print_status ".env file already exists"
fi

# Generate application key
print_status "Generating application key..."
php artisan key:generate
if [ $? -eq 0 ]; then
    print_success "Application key generated"
else
    print_error "Failed to generate application key"
    exit 1
fi

echo ""
echo -e "${BOLD}Step 5: Setting Up Database${NC}"
echo "────────────────────────────────────────"

# Ask user for database type
echo ""
echo "  Select database type:"
echo "  1) SQLite (recommended for development)"
echo "  2) MySQL/MariaDB"
echo ""
echo -ne "  Enter choice [1-2]: "
read DB_CHOICE

if [ "$DB_CHOICE" = "1" ]; then
    # SQLite setup
    print_status "Setting up SQLite database..."
    
    # Update .env for SQLite
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i 's/^# DB_HOST=.*/# DB_HOST=127.0.0.1/' .env
    sed -i 's/^# DB_PORT=.*/# DB_PORT=3306/' .env
    sed -i 's/^# DB_DATABASE=.*/# DB_DATABASE=laravel/' .env
    sed -i 's/^# DB_USERNAME=.*/# DB_USERNAME=root/' .env
    sed -i 's/^# DB_PASSWORD=.*/# DB_PASSWORD=/' .env
    
    # Create SQLite database file
    touch database/database.sqlite
    print_success "SQLite database created"
    
elif [ "$DB_CHOICE" = "2" ]; then
    # MySQL setup
    print_status "Setting up MySQL database..."
    echo ""
    echo -ne "  Database name [laravel]: "
    read DB_NAME
    DB_NAME=${DB_NAME:-laravel}
    
    echo -ne "  Database username [root]: "
    read DB_USER
    DB_USER=${DB_USER:-root}
    
    echo -ne "  Database password: "
    read -s DB_PASS
    echo ""
    
    # Update .env for MySQL
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i "s/^# DB_HOST=.*/DB_HOST=127.0.0.1/" .env
    sed -i "s/^# DB_PORT=.*/DB_PORT=3306/" .env
    sed -i "s/^# DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/^# DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/^# DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
    
    print_success "MySQL configuration saved"
    print_status "Please create the database '$DB_NAME' in MySQL before running migrations"
    echo -e "  ${YELLOW}CREATE DATABASE $DB_NAME;${NC}"
    
else
    print_error "Invalid choice. Using SQLite by default."
    touch database/database.sqlite
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
fi

echo ""
echo -e "${BOLD}Step 6: Running Database Migrations${NC}"
echo "────────────────────────────────────────"
print_status "Running migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_success "Database migrations completed"
else
    print_error "Failed to run migrations"
    echo -e "  ${YELLOW}Make sure database is properly configured${NC}"
    exit 1
fi

echo ""
echo -e "${BOLD}Step 7: Seeding Admin User${NC}"
echo "────────────────────────────────────────"
print_status "Running admin user seeder..."
php artisan db:seed --class=AdminUserSeeder --force
if [ $? -eq 0 ]; then
    print_success "Admin user created"
else
    print_status "Admin user may already exist"
fi

# Seed database (optional)
echo ""
echo -ne "  Run additional database seeders? (y/n) [n]: "
read SEED_DB
if [ "$SEED_DB" = "y" ] || [ "$SEED_DB" = "Y" ]; then
    print_status "Running database seeders..."
    php artisan db:seed --force
    if [ $? -eq 0 ]; then
        print_success "Database seeded"
    else
        print_error "Failed to seed database"
    fi
fi

echo ""
echo -e "${BOLD}Step 8: Building Frontend Assets${NC}"
echo "────────────────────────────────────────"
print_status "Building assets with npm..."
npm run build
if [ $? -eq 0 ]; then
    print_success "Frontend assets built"
else
    print_error "Failed to build assets"
    exit 1
fi

echo ""
echo -e "${BOLD}Step 9: Creating Storage Links${NC}"
echo "────────────────────────────────────────"
print_status "Creating storage link..."
php artisan storage:link
if [ $? -eq 0 ]; then
    print_success "Storage link created"
else
    print_status "Storage link may already exist"
fi

echo ""
echo -e "${BOLD}Step 10: Clearing Caches${NC}"
echo "────────────────────────────────────────"
print_status "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
print_success "Caches cleared"

echo ""
echo "════════════════════════════════════════"
echo -e "  ${GREEN}${BOLD}Setup Complete!${NC}"
echo "════════════════════════════════════════"
echo ""
echo "Your Nexus project is ready!"
echo ""
echo -e "${CYAN}Admin Login Credentials:${NC}"
echo "  Email:    admin@example.com"
echo "  Password: admin123"
echo "  Username: admin"
echo ""
echo "To start the development server:"
echo -e "  ${CYAN}php artisan serve${NC}"
echo ""
echo "Or to start with tunnel (for public URL):"
echo -e "  ${CYAN}./start-tunnel.sh${NC}"
echo ""
echo -e "${GREEN}Enjoy! 🚀${NC}"
echo ""
