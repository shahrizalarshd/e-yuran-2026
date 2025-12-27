# 🚀 PANDUAN CI/CD: GitHub Actions + DigitalOcean
## Sistem e-Yuran Taman Tropika Kajang

---

## 📋 ISI KANDUNGAN

1. [Setup DigitalOcean Droplet](#1-setup-digitalocean-droplet)
2. [Setup Server (LEMP Stack)](#2-setup-server-lemp-stack)
3. [Configure GitHub Repository](#3-configure-github-repository)
4. [Setup GitHub Actions Secrets](#4-setup-github-actions-secrets)
5. [Deploy Pertama Kali](#5-deploy-pertama-kali)
6. [Automated Deployment](#6-automated-deployment)
7. [Monitoring & Logs](#7-monitoring--logs)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. SETUP DIGITALOCEAN DROPLET

### A. Buat Droplet Baru

1. **Login ke DigitalOcean Dashboard**
   - https://cloud.digitalocean.com

2. **Create → Droplets**

3. **Choose Configuration:**
   ```
   Distribution: Ubuntu 22.04 LTS x64
   Plan: Basic
   CPU Options: Regular (Shared CPU)
   Size: $12/mo (2GB RAM, 50GB SSD) - MINIMUM
         $18/mo (2GB RAM, 60GB SSD) - RECOMMENDED
   Datacenter: Singapore (closest to Malaysia)
   ```

4. **Authentication:**
   - ✅ **SSH Keys** (Recommended)
   - Generate SSH key di local machine:
   ```bash
   ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
   cat ~/.ssh/id_rsa.pub
   ```
   - Copy output dan paste ke DigitalOcean

5. **Hostname:**
   ```
   eyuran-pptt-production
   ```

6. **Create Droplet** → Wait 60 seconds

### B. First Login

```bash
# Get IP address from DigitalOcean dashboard
ssh root@YOUR_DROPLET_IP

# Update system
apt update && apt upgrade -y
```

---

## 2. SETUP SERVER (LEMP STACK)

### A. Install Nginx

```bash
apt install nginx -y
systemctl enable nginx
systemctl start nginx

# Check
systemctl status nginx
```

### B. Install PHP 8.2

```bash
# Add repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP & extensions
apt install php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl php8.2-sqlite3 -y

# Check
php -v
```

### C. Install MySQL

```bash
# Install
apt install mysql-server -y

# Secure installation
mysql_secure_installation

# Login as root
mysql -u root -p

# Create database & user
CREATE DATABASE e_yuran_pptt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eyuran_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON e_yuran_pptt.* TO 'eyuran_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### D. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Check
composer --version
```

### E. Install Node.js & NPM

```bash
# Install NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc

# Install Node 20 LTS
nvm install 20
nvm use 20
nvm alias default 20

# Check
node -v
npm -v
```

### F. Install Git

```bash
apt install git -y
git --version
```

### G. Setup Firewall

```bash
# Configure UFW
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable

# Check
ufw status
```

---

## 3. SETUP PROJECT DI SERVER

### A. Create Deployment User

```bash
# Create user
adduser deployer
usermod -aG www-data deployer
usermod -aG sudo deployer

# Switch to deployer
su - deployer
```

### B. Setup SSH Keys untuk Deployer

```bash
# Di server (as deployer)
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Di local machine, copy public key
cat ~/.ssh/id_rsa.pub

# Di server, paste ke authorized_keys
nano ~/.ssh/authorized_keys
# Paste public key
chmod 600 ~/.ssh/authorized_keys

# Test dari local
ssh deployer@YOUR_DROPLET_IP
```

### C. Clone Repository

```bash
# Di server (as deployer)
cd /var/www
sudo mkdir e-yuran
sudo chown deployer:www-data e-yuran

# Setup Git credentials untuk private repo
git config --global credential.helper store

# Clone
git clone https://github.com/YOUR_USERNAME/e-yuran-2026.git e-yuran
cd e-yuran
```

### D. Setup Environment

```bash
# Copy .env
cp .env.example .env
nano .env
```

**Edit .env.production:**
```env
APP_NAME="e-Yuran PPTT"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eyuran.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_yuran_pptt
DB_USERNAME=eyuran_user
DB_PASSWORD=StrongPassword123!

# ToyyibPay (Production)
TOYYIBPAY_SECRET_KEY=your_production_secret_key
TOYYIBPAY_CATEGORY_CODE=your_category_code
TOYYIBPAY_SANDBOX=false

# Telegram (Optional)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
TELEGRAM_ERROR_NOTIFICATION=true

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

### E. Install Dependencies & Setup

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci
npm run build

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Import legacy data (FIRST TIME ONLY!)
php artisan import:legacy-data --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set permissions
sudo chown -R www-data:www-data /var/www/e-yuran/storage
sudo chown -R www-data:www-data /var/www/e-yuran/bootstrap/cache
sudo chmod -R 775 /var/www/e-yuran/storage
sudo chmod -R 775 /var/www/e-yuran/bootstrap/cache
```

---

## 4. CONFIGURE NGINX

### A. Create Nginx Config

```bash
sudo nano /etc/nginx/sites-available/eyuran
```

**Paste configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name eyuran.yourdomain.com www.eyuran.yourdomain.com;
    root /var/www/e-yuran/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase upload size for Excel files
    client_max_body_size 20M;
}
```

### B. Enable Site

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/eyuran /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### C. Setup SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d eyuran.yourdomain.com -d www.eyuran.yourdomain.com

# Auto-renewal (already setup by certbot)
sudo systemctl status certbot.timer
```

---

## 5. SETUP GITHUB REPOSITORY

### A. Add Deployment Key (Server SSH Key)

**Di server:**
```bash
# Generate SSH key untuk deployment
ssh-keygen -t rsa -b 4096 -C "deploy@eyuran-server"
cat ~/.ssh/id_rsa.pub
```

**Di GitHub:**
1. Repository → Settings → Deploy keys
2. Add deploy key
3. Paste public key
4. ✅ Allow write access
5. Add key

### B. Setup GitHub Secrets

**Di GitHub:**
1. Repository → Settings → Secrets and variables → Actions
2. **New repository secret**

**Add these secrets:**

| Secret Name | Value | Keterangan |
|------------|-------|------------|
| `DO_HOST` | `YOUR_DROPLET_IP` | IP address droplet |
| `DO_USERNAME` | `deployer` | SSH username |
| `DO_SSH_KEY` | `PRIVATE_KEY_CONTENT` | Private SSH key |
| `DO_PORT` | `22` | SSH port |
| `TELEGRAM_BOT_TOKEN` | `your_bot_token` | Optional notification |
| `TELEGRAM_CHAT_ID` | `your_chat_id` | Optional notification |

**Cara dapat private key:**
```bash
# Di local machine
cat ~/.ssh/id_rsa

# Copy SELURUH output (termasuk BEGIN/END lines)
```

---

## 6. SETUP SUPERVISOR (Queue Worker)

```bash
# Install Supervisor
sudo apt install supervisor -y

# Create config
sudo nano /etc/supervisor/conf.d/eyuran-worker.conf
```

**Paste:**
```ini
[program:eyuran-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/e-yuran/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/e-yuran/storage/logs/worker.log
stopwaitsecs=3600
```

**Start:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eyuran-worker:*
sudo supervisorctl status
```

---

## 7. SETUP CRON (Laravel Scheduler)

```bash
# Edit crontab untuk www-data user
sudo crontab -e -u www-data
```

**Add:**
```bash
* * * * * cd /var/www/e-yuran && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. AUTOMATED DEPLOYMENT

### A. Workflow Triggers

**Workflows yang telah disediakan:**

1. **`.github/workflows/tests.yml`**
   - Trigger: Pull requests, push ke non-main branches
   - Action: Run tests, code quality checks
   - Matrix testing: PHP 8.2 & 8.3

2. **`.github/workflows/deploy.yml`**
   - Trigger: Push ke `main` atau `production` branch
   - Action: Run tests → Deploy ke server
   - Auto maintenance mode, migrations, cache clear

### B. Deployment Flow

```
Developer Push to GitHub
         ↓
GitHub Actions: Run Tests
         ↓
    Tests Pass?
    ↙        ↘
   NO        YES
    ↓         ↓
 Notify    SSH to Server
  Fail      ↓
         Enable Maintenance Mode
              ↓
         Git Pull Latest Code
              ↓
         Composer Install
              ↓
         NPM Build Assets
              ↓
         Run Migrations
              ↓
         Clear & Cache Config
              ↓
         Restart Queue Workers
              ↓
         Disable Maintenance Mode
              ↓
         Notify Success ✅
```

### C. Manual Deployment

Jika perlu deploy manually:

```bash
# SSH ke server
ssh deployer@YOUR_DROPLET_IP

cd /var/www/e-yuran

# Enable maintenance
php artisan down

# Pull latest
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Migrate
php artisan migrate --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart workers
php artisan queue:restart

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Disable maintenance
php artisan up
```

---

## 9. MONITORING & LOGS

### A. Application Logs

```bash
# Laravel logs
tail -f /var/www/e-yuran/storage/logs/laravel.log

# Worker logs
tail -f /var/www/e-yuran/storage/logs/worker.log

# Nginx access logs
sudo tail -f /var/log/nginx/access.log

# Nginx error logs
sudo tail -f /var/log/nginx/error.log
```

### B. Check Services Status

```bash
# Nginx
sudo systemctl status nginx

# PHP-FPM
sudo systemctl status php8.2-fpm

# MySQL
sudo systemctl status mysql

# Supervisor
sudo supervisorctl status

# Queue workers
sudo supervisorctl status eyuran-worker:*
```

### C. Database Backup

**Create backup script:**
```bash
sudo nano /usr/local/bin/backup-eyuran.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/eyuran"
DB_NAME="e_yuran_pptt"
DB_USER="eyuran_user"
DB_PASS="StrongPassword123!"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p"$DB_PASS" $DB_NAME | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup storage folder
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" /var/www/e-yuran/storage/app/private

# Keep last 30 days only
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +30 -delete

echo "✅ Backup completed: $DATE"
```

**Make executable & add to cron:**
```bash
sudo chmod +x /usr/local/bin/backup-eyuran.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

```
0 2 * * * /usr/local/bin/backup-eyuran.sh >> /var/log/eyuran-backup.log 2>&1
```

---

## 10. SECURITY CHECKLIST

### A. Server Security

```bash
# Change SSH port (optional but recommended)
sudo nano /etc/ssh/sshd_config
# Change Port 22 to Port 2222
sudo systemctl restart sshd

# Disable root login
sudo nano /etc/ssh/sshd_config
# Set: PermitRootLogin no
sudo systemctl restart sshd

# Install fail2ban
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### B. Application Security

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong database passwords
- [ ] `.env` file not accessible (outside public_html)
- [ ] File permissions correct (755/644)
- [ ] CSRF protection enabled
- [ ] SSL certificate valid
- [ ] ToyyibPay production mode
- [ ] Regular backups scheduled

---

## 11. TROUBLESHOOTING

### Problem: GitHub Actions Cannot Connect to Server

**Check:**
```bash
# Test SSH connection
ssh -i ~/.ssh/id_rsa deployer@YOUR_DROPLET_IP

# Check SSH key permissions
ls -la ~/.ssh/
chmod 600 ~/.ssh/id_rsa
chmod 644 ~/.ssh/id_rsa.pub
```

### Problem: Permission Denied During Deployment

**Fix:**
```bash
# On server
sudo chown -R deployer:www-data /var/www/e-yuran
sudo chmod -R 775 /var/www/e-yuran/storage
sudo chmod -R 775 /var/www/e-yuran/bootstrap/cache
```

### Problem: 500 Internal Server Error

**Check:**
```bash
# Laravel logs
tail -50 /var/www/e-yuran/storage/logs/laravel.log

# Nginx logs
sudo tail -50 /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -50 /var/log/php8.2-fpm.log

# Fix permissions
cd /var/www/e-yuran
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Problem: Database Connection Failed

**Check:**
```bash
# Test MySQL connection
mysql -u eyuran_user -p

# Check .env file
cat /var/www/e-yuran/.env | grep DB_

# Restart MySQL
sudo systemctl restart mysql
```

### Problem: Queue Not Processing

**Check:**
```bash
# Check supervisor status
sudo supervisorctl status eyuran-worker:*

# Restart workers
sudo supervisorctl restart eyuran-worker:*

# Check worker logs
tail -50 /var/www/e-yuran/storage/logs/worker.log
```

---

## 12. DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] All tests passing locally
- [ ] Legacy data imported (first time only)
- [ ] `.env` configured correctly
- [ ] Database migrated
- [ ] SSL certificate installed
- [ ] Backup created

### During Deployment
- [ ] Maintenance mode activated
- [ ] Code pulled successfully
- [ ] Dependencies installed
- [ ] Assets built
- [ ] Migrations run
- [ ] Cache cleared and rebuilt
- [ ] Workers restarted
- [ ] Maintenance mode deactivated

### Post-Deployment
- [ ] Website accessible
- [ ] Admin can login
- [ ] Dashboard loads correctly
- [ ] ToyyibPay working
- [ ] Logs checked for errors
- [ ] Queue processing
- [ ] Email sending (if applicable)

---

## 📞 QUICK REFERENCE

```bash
# SSH to server
ssh deployer@YOUR_DROPLET_IP

# Check application
cd /var/www/e-yuran
php artisan about

# View logs
tail -f storage/logs/laravel.log

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart eyuran-worker:*

# Manual deployment
./deploy.sh
```

---

## 🎉 SELESAI!

Sistem anda sekarang:
- ✅ Auto-deploy bila push ke `main`
- ✅ Auto-testing untuk PR
- ✅ Maintenance mode automatically
- ✅ Zero-downtime deployment
- ✅ Telegram notifications
- ✅ Daily backups
- ✅ Queue workers monitored
- ✅ SSL secured

**Happy Deploying!** 🚀



