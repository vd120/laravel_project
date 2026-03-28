#!/bin/bash

# ============================================
# Nexus - Automated Test Runner
# ============================================

set -e

echo "Nexus - Automated Test Runner"
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "✗ .env file not found"
    echo "  Creating .env from .env.example..."
    cp .env.example .env
fi

# Check if app key exists
if [ -z "$(grep '^APP_KEY=' .env | cut -d'=' -f2)" ]; then
    echo "⚠ Application key not set"
    echo "  Generating application key..."
    php artisan key:generate
fi

# Clear caches
echo "● Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run tests
echo ""
echo "● Running tests..."
echo ""

if php artisan test --parallel; then
    echo ""
    echo "All tests passed!"
    exit 0
else
    echo ""
    echo "Some tests failed"
    echo ""
    echo "Troubleshooting tips:"
    echo "  1. Check database connection in .env"
    echo "  2. Run 'php artisan migrate:fresh --seed'"
    echo "  3. Check storage/logs/laravel.log for errors"
    echo "  4. Run 'php artisan nexus:troubleshoot --fix'"
    echo ""
    exit 1
fi
