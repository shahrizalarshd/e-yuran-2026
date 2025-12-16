#!/bin/bash

# ============================================
# E-YURAN PPTT - SERVER SETUP SCRIPT
# Run this on DigitalOcean server as deployer
# ============================================

set -e

echo "╔════════════════════════════════════════════╗"
echo "║   E-YURAN PPTT - Server Setup              ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info() { echo -e "${GREEN}✓${NC} $1"; }
warn() { echo -e "${YELLOW}⚠${NC} $1"; }

# Check if in correct directory
if [ ! -f "artisan" ]; then
    echo "ERROR: Please run this script from /var/www/e-yuran directory"
    echo "Run: cd /var/www/e-yuran && bash server-setup.sh"
    exit 1
fi

echo "Step 1: Setting up .env file..."
if [ ! -f ".env" ]; then
    cp env.production.template .env 2>/dev/null || cp .env.example .env
    info ".env file created"
else
    info ".env file already exists"
fi

# Update .env with correct database password
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=AppUser@2024!Secure#PPTT/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
info ".env database password updated"

echo ""
echo "Step 2: Generating application key..."
php artisan key:generate --force
info "Application key generated"

echo ""
echo "Step 3: Installing NPM dependencies..."
npm install --legacy-peer-deps
info "NPM dependencies installed"

echo ""
echo "Step 4: Building assets..."
npm run build
info "Assets built"

echo ""
echo "Step 5: Running database migrations..."
php artisan migrate --force
info "Database migrated"

echo ""
echo "Step 6: Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
info "Permissions set"

echo ""
echo "Step 7: Clearing and caching config..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
info "Config cached"

echo ""
echo "╔════════════════════════════════════════════╗"
echo "║   ✅ SETUP COMPLETE!                       ║"
echo "╚════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. Configure Nginx (see GITHUB_DIGITALOCEAN_SETUP.md)"
echo "2. Setup SSL with: sudo certbot --nginx"
echo "3. Create admin user with: php artisan tinker"
echo ""

