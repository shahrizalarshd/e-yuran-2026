#!/bin/bash

# ============================================
# E-YURAN PPTT - Manual Deployment Script
# ============================================

set -e

echo "╔════════════════════════════════════════════╗"
echo "║   E-YURAN PPTT - Manual Deployment        ║"
echo "║   Taman Tropika Kajang                    ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
info() {
    echo -e "${GREEN}✓${NC} $1"
}

warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
    exit 1
}

# Check if running as correct user
if [ "$EUID" -eq 0 ]; then 
    error "Please do not run as root. Run as deployer user."
fi

# Confirm deployment
echo "This will deploy the latest code to production."
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    warn "Deployment cancelled."
    exit 0
fi

echo ""
echo "Starting deployment..."
echo ""

# Enable maintenance mode
info "Enabling maintenance mode..."
php artisan down --message="System upgrade in progress. Please wait..." --retry=60 || error "Failed to enable maintenance mode"

# Pull latest code
info "Pulling latest code from Git..."
git pull origin main || error "Failed to pull from Git"

# Install/update Composer dependencies
info "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction || error "Composer install failed"

# Install/update NPM dependencies
info "Installing NPM dependencies..."
npm ci || error "NPM install failed"

# Build assets
info "Building production assets..."
npm run build || error "Asset build failed"

# Run database migrations
info "Running database migrations..."
php artisan migrate --force || error "Migration failed"

# Clear cache
info "Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize application
info "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
info "Restarting queue workers..."
php artisan queue:restart

# Fix permissions
info "Setting correct permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Disable maintenance mode
info "Disabling maintenance mode..."
php artisan up || error "Failed to disable maintenance mode"

echo ""
echo "╔════════════════════════════════════════════╗"
echo "║   ✅ DEPLOYMENT COMPLETED SUCCESSFULLY     ║"
echo "╚════════════════════════════════════════════╝"
echo ""
info "Website: https://eyuran.yourdomain.com"
info "Check logs: tail -f storage/logs/laravel.log"
echo ""



