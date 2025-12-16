# 🚀 SETUP GITHUB ACTIONS + DIGITALOCEAN
## Panduan Lengkap untuk e-Yuran PPTT

---

## 📖 OVERVIEW

Sistem ini menggunakan **automated CI/CD pipeline** yang akan:
- ✅ Auto-test setiap code changes
- ✅ Auto-deploy bila push ke `main` branch
- ✅ Zero-downtime deployment
- ✅ Notification via Telegram

**Workflow:**
```
Code Push → GitHub → Run Tests → Deploy to DigitalOcean → Notify Success
```

---

## 🎯 TAHAP 1: SETUP DIGITALOCEAN DROPLET

### 1.1 Create Account & Droplet

```
1. Pergi ke: https://www.digitalocean.com
2. Sign up / Login
3. Click: Create → Droplets
```

### 1.2 Configuration

**Choose an image:**
```
✓ Ubuntu 22.04 (LTS) x64
```

**Choose a plan:**
```
✓ Basic
✓ Regular CPU (Shared)
✓ $12/month: 2GB RAM / 1 CPU / 50GB SSD (MINIMUM)
✓ $18/month: 2GB RAM / 1 CPU / 60GB SSD (RECOMMENDED)
```

**Choose a datacenter:**
```
✓ Singapore (closest to Malaysia - faster!)
```

**Authentication:**
```
✓ SSH Keys (RECOMMENDED)
```

**Generate SSH Key** (di local machine):
```bash
# Mac/Linux
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

# Lokasi: ~/.ssh/id_rsa
# Tekan Enter untuk default location
# Optional: Set passphrase untuk extra security

# Copy public key
cat ~/.ssh/id_rsa.pub
```

**Add SSH Key to DigitalOcean:**
```
1. Copy output dari command di atas
2. Di DigitalOcean, click "New SSH Key"
3. Paste public key
4. Name: "My Mac" atau "Development Machine"
5. Add SSH Key
```

**Finalize:**
```
Hostname: eyuran-pptt-production
Tags: production, laravel, eyuran
Project: (create new project "e-Yuran PPTT")

Create Droplet → Wait ~60 seconds
```

### 1.3 First Login

```bash
# Get IP from DigitalOcean dashboard
ssh root@YOUR_DROPLET_IP

# Should login without password (using SSH key)
# If successful, you'll see Ubuntu welcome message
```

---

## 🎯 TAHAP 2: SETUP SERVER

### 2.1 Update System

```bash
# Update package lists
apt update

# Upgrade all packages
apt upgrade -y

# Reboot (optional but recommended)
reboot
```

### 2.2 Install Nginx

```bash
# Install
apt install nginx -y

# Start & enable
systemctl start nginx
systemctl enable nginx

# Check status
systemctl status nginx

# Should see: "active (running)"

# Test: Open browser
# Visit: http://YOUR_DROPLET_IP
# Should see: "Welcome to nginx!"
```

### 2.3 Install PHP 8.2

```bash
# Add PHP repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP & required extensions
apt install -y \
  php8.2-fpm \
  php8.2-cli \
  php8.2-common \
  php8.2-mysql \
  php8.2-zip \
  php8.2-gd \
  php8.2-mbstring \
  php8.2-curl \
  php8.2-xml \
  php8.2-bcmath \
  php8.2-intl \
  php8.2-sqlite3

# Verify
php -v
# Should see: PHP 8.2.x

# Start PHP-FPM
systemctl start php8.2-fpm
systemctl enable php8.2-fpm
```

### 2.4 Install MySQL

```bash
# Install
apt install mysql-server -y

# Secure installation
mysql_secure_installation

# Answers:
# - Validate password: Y
# - Password strength: 2 (Strong)
# - Set root password: YES (use strong password!)
# - Remove anonymous users: Y
# - Disallow root login remotely: Y
# - Remove test database: Y
# - Reload privilege tables: Y

# Login to MySQL
mysql -u root -p
# Enter password yang baru set
```

**Create Database & User:**
```sql
CREATE DATABASE e_yuran_pptt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'eyuran_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';

GRANT ALL PRIVILEGES ON e_yuran_pptt.* TO 'eyuran_user'@'localhost';

FLUSH PRIVILEGES;

EXIT;
```

### 2.5 Install Composer

```bash
# Download
curl -sS https://getcomposer.org/installer | php

# Move to global
mv composer.phar /usr/local/bin/composer

# Make executable
chmod +x /usr/local/bin/composer

# Verify
composer --version
# Should see: Composer version 2.x.x
```

### 2.6 Install Node.js & NPM

```bash
# Install NVM (Node Version Manager)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# Reload shell
source ~/.bashrc

# Install Node 20 (LTS)
nvm install 20

# Use Node 20
nvm use 20

# Set as default
nvm alias default 20

# Verify
node -v  # Should see: v20.x.x
npm -v   # Should see: 10.x.x
```

### 2.7 Install Git

```bash
apt install git -y

# Configure
git config --global user.name "Your Name"
git config --global user.email "your@email.com"

# Verify
git --version
```

### 2.8 Setup Firewall

```bash
# Allow SSH
ufw allow OpenSSH

# Allow HTTP & HTTPS
ufw allow 'Nginx Full'

# Enable firewall
ufw enable

# Check status
ufw status

# Should see:
# OpenSSH    ALLOW    Anywhere
# Nginx Full ALLOW    Anywhere
```

---

## 🎯 TAHAP 3: SETUP DEPLOYMENT USER

### 3.1 Create Deployer User

```bash
# Create user
adduser deployer

# Set password (use strong password!)
# Fill in other details (optional)

# Add to www-data group
usermod -aG www-data deployer

# Add sudo privileges
usermod -aG sudo deployer
```

### 3.2 Setup SSH for Deployer

```bash
# Switch to deployer
su - deployer

# Create .ssh directory
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Exit back to root
exit

# Copy authorized_keys from root to deployer
cp /root/.ssh/authorized_keys /home/deployer/.ssh/
chown deployer:deployer /home/deployer/.ssh/authorized_keys
chmod 600 /home/deployer/.ssh/authorized_keys

# Test from local machine
ssh deployer@YOUR_DROPLET_IP
# Should login successfully
```

---

## 🎯 TAHAP 4: DEPLOY APPLICATION MANUALLY (FIRST TIME)

### 4.1 Create Project Directory

```bash
# As deployer
ssh deployer@YOUR_DROPLET_IP

cd /var/www

# Create directory
sudo mkdir e-yuran

# Set ownership
sudo chown deployer:www-data e-yuran

cd e-yuran
```

### 4.2 Setup GitHub Access

**Option A: Personal Access Token (for private repo)**
```bash
# Generate token di GitHub:
# Settings → Developer settings → Personal access tokens → Generate new token
# Scope: repo (full control)

# Clone dengan token
git clone https://YOUR_TOKEN@github.com/YOUR_USERNAME/e-yuran-2026.git .
```

**Option B: Deploy Key**
```bash
# Generate SSH key for deployment
ssh-keygen -t rsa -b 4096 -C "deploy@server"

# Save to: /home/deployer/.ssh/id_rsa_github
# No passphrase (for automation)

# Copy public key
cat /home/deployer/.ssh/id_rsa_github.pub

# Add to GitHub:
# Repository → Settings → Deploy keys → Add deploy key
# Paste key, name it "Production Server"
# ✓ Allow write access

# Configure SSH
nano ~/.ssh/config
```

Add:
```
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_rsa_github
```

```bash
# Set permissions
chmod 600 ~/.ssh/config

# Test connection
ssh -T git@github.com
# Should see: "Hi username! You've successfully authenticated..."

# Clone
git clone git@github.com:YOUR_USERNAME/e-yuran-2026.git .
```

### 4.3 Setup Environment

```bash
cd /var/www/e-yuran

# Copy template
cp env.production.template .env

# Edit
nano .env
```

**Configure .env:**
```env
APP_URL=https://eyuran.yourdomain.com
DB_DATABASE=e_yuran_pptt
DB_USERNAME=eyuran_user
DB_PASSWORD=StrongPassword123!
TOYYIBPAY_SECRET_KEY=your_production_key
TOYYIBPAY_CATEGORY_CODE=your_category
TOYYIBPAY_SANDBOX=false
```

### 4.4 Install & Setup

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci

# Generate key
php artisan key:generate

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Import legacy data (FIRST TIME ONLY!)
php artisan import:legacy-data --force

# Verify
php artisan verify:legacy-data

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🎯 TAHAP 5: CONFIGURE NGINX

### 5.1 Create Site Configuration

```bash
sudo nano /etc/nginx/sites-available/eyuran
```

**Paste (ubah domain):**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name eyuran.yourdomain.com www.eyuran.yourdomain.com;
    
    root /var/www/e-yuran/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Increase upload size
    client_max_body_size 20M;

    # Logs
    access_log /var/log/nginx/eyuran-access.log;
    error_log /var/log/nginx/eyuran-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { 
        access_log off; 
        log_not_found off; 
    }
    
    location = /robots.txt { 
        access_log off; 
        log_not_found off; 
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5.2 Enable Site

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/eyuran /etc/nginx/sites-enabled/

# Remove default
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Should see: "syntax is ok" & "test is successful"

# Restart Nginx
sudo systemctl restart nginx

# Check status
sudo systemctl status nginx
```

### 5.3 Configure DNS

**Di domain provider (e.g., Namecheap, GoDaddy):**
```
Type: A Record
Host: @
Value: YOUR_DROPLET_IP
TTL: Automatic

Type: A Record
Host: www
Value: YOUR_DROPLET_IP
TTL: Automatic
```

**Wait 5-30 minutes untuk DNS propagate**

**Test:**
```bash
# Check if domain points to your server
ping eyuran.yourdomain.com
# Should show your droplet IP
```

### 5.4 Install SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d eyuran.yourdomain.com -d www.eyuran.yourdomain.com

# Enter:
# Email: your@email.com
# Terms: A (Agree)
# Share email: N (No)
# Redirect: 2 (Redirect HTTP to HTTPS)

# Test auto-renewal
sudo certbot renew --dry-run

# Should see: "Congratulations, all simulated renewals succeeded"
```

**Visit:** https://eyuran.yourdomain.com
**Should see your application!** 🎉

---

## 🎯 TAHAP 6: SETUP GITHUB ACTIONS

### 6.1 Generate SSH Key for GitHub Actions

```bash
# On server (as deployer)
ssh-keygen -t rsa -b 4096 -C "github-actions@eyuran"

# Save to: /home/deployer/.ssh/id_rsa_actions
# No passphrase

# Add to authorized_keys
cat ~/.ssh/id_rsa_actions.pub >> ~/.ssh/authorized_keys

# Get private key (for GitHub Secrets)
cat ~/.ssh/id_rsa_actions
# Copy ENTIRE output (including BEGIN/END lines)
```

### 6.2 Add GitHub Secrets

**Pergi ke GitHub:**
```
Repository → Settings → Secrets and variables → Actions → New repository secret
```

**Add these secrets:**

| Name | Value |
|------|-------|
| `DO_HOST` | `YOUR_DROPLET_IP` |
| `DO_USERNAME` | `deployer` |
| `DO_SSH_KEY` | (paste private key yang copy tadi) |
| `DO_PORT` | `22` |
| `TELEGRAM_BOT_TOKEN` | (optional) |
| `TELEGRAM_CHAT_ID` | (optional) |

### 6.3 Verify Workflows

Files yang telah disediakan:
- `.github/workflows/tests.yml` ✅
- `.github/workflows/deploy.yml` ✅

**Check:**
```bash
# Local machine
cd /path/to/e-yuran-2026

ls -la .github/workflows/
# Should see: deploy.yml, tests.yml
```

---

## 🎯 TAHAP 7: TEST DEPLOYMENT

### 7.1 Make Small Change

```bash
# Local machine
cd /path/to/e-yuran-2026

# Make a small change
echo "<!-- Test deployment -->" >> resources/views/welcome.blade.php

# Commit
git add .
git commit -m "test: verify automated deployment"

# Push
git push origin main
```

### 7.2 Watch Deployment

```bash
# Go to GitHub
# Repository → Actions

# You should see workflow running:
# "test: verify automated deployment"

# Click on it to see progress
# Should see:
# - Run Tests ✓
# - Deploy to DigitalOcean ✓
```

### 7.3 Verify Success

**Check website:**
```
Visit: https://eyuran.yourdomain.com
Should see your changes!
```

**Check server logs:**
```bash
ssh deployer@YOUR_DROPLET_IP
cd /var/www/e-yuran
tail -50 storage/logs/laravel.log
```

---

## 🎯 TAHAP 8: SETUP QUEUE WORKER (SUPERVISOR)

### 8.1 Install Supervisor

```bash
sudo apt install supervisor -y
```

### 8.2 Create Worker Configuration

```bash
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

### 8.3 Start Worker

```bash
# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start eyuran-worker:*

# Check status
sudo supervisorctl status

# Should see:
# eyuran-worker:eyuran-worker_00   RUNNING
# eyuran-worker:eyuran-worker_01   RUNNING
```

---

## 🎯 TAHAP 9: SETUP CRON (SCHEDULER)

```bash
# Edit crontab
sudo crontab -e -u www-data

# Choose editor (nano = 1)
```

**Add:**
```
* * * * * cd /var/www/e-yuran && php artisan schedule:run >> /dev/null 2>&1
```

**Save & exit**

---

## 🎯 TAHAP 10: SETUP BACKUP

### 10.1 Create Backup Script

```bash
sudo nano /usr/local/bin/backup-eyuran.sh
```

**Paste:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/eyuran"
DB_NAME="e_yuran_pptt"
DB_USER="eyuran_user"
DB_PASS="StrongPassword123!"

# Create directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p"$DB_PASS" $DB_NAME | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup storage
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" /var/www/e-yuran/storage/app/private

# Keep last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +30 -delete

echo "✅ Backup completed: $DATE"
```

### 10.2 Make Executable & Schedule

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-eyuran.sh

# Test
sudo /usr/local/bin/backup-eyuran.sh

# Should see: "✅ Backup completed: ..."

# Schedule daily at 2 AM
sudo crontab -e
```

**Add:**
```
0 2 * * * /usr/local/bin/backup-eyuran.sh >> /var/log/eyuran-backup.log 2>&1
```

---

## ✅ CHECKLIST DEPLOYMENT

### Pre-Deployment
- [ ] DigitalOcean account created
- [ ] Droplet provisioned (2GB RAM minimum)
- [ ] SSH key generated & added
- [ ] Domain purchased & DNS configured

### Server Setup
- [ ] Nginx installed & running
- [ ] PHP 8.2 installed
- [ ] MySQL installed & database created
- [ ] Composer installed
- [ ] Node.js installed
- [ ] Firewall configured (UFW)
- [ ] Deployer user created

### Application
- [ ] Code cloned to /var/www/e-yuran
- [ ] .env configured
- [ ] Dependencies installed
- [ ] Database migrated
- [ ] Legacy data imported
- [ ] Permissions set correctly

### Web Server
- [ ] Nginx site configured
- [ ] SSL certificate installed
- [ ] HTTPS redirect working
- [ ] Website accessible

### GitHub Actions
- [ ] Workflows files present
- [ ] GitHub Secrets configured
- [ ] Test deployment successful
- [ ] Auto-deploy working

### Background Services
- [ ] Supervisor installed & running
- [ ] Queue workers running
- [ ] Cron job configured
- [ ] Backup script scheduled

### Final Checks
- [ ] Can login as Super Admin
- [ ] Dashboard loads correctly
- [ ] Can create/view bills
- [ ] Payment gateway works
- [ ] No errors in logs
- [ ] Telegram notifications (optional)

---

## 🎉 SELESAI!

Sistem anda sekarang:
- ✅ **Fully automated** - Push ke main = auto deploy
- ✅ **Zero-downtime** - Maintenance mode during deploy
- ✅ **Self-healing** - Queue workers auto-restart
- ✅ **Secured** - SSL, Firewall, proper permissions
- ✅ **Backed up** - Daily automatic backups
- ✅ **Monitored** - Logs, notifications

**Next Steps:**
1. Create Super Admin user
2. Configure ToyyibPay production keys
3. Test all critical features
4. Inform residents
5. Go live! 🚀

---

## 📞 QUICK COMMANDS

```bash
# SSH to server
ssh deployer@YOUR_DROPLET_IP

# View logs
tail -f /var/www/e-yuran/storage/logs/laravel.log

# Manual deployment
cd /var/www/e-yuran && ./deploy.sh

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart eyuran-worker:*

# Check status
sudo supervisorctl status
sudo systemctl status nginx
php artisan about
```

---

**🎊 Tahniah! Sistem e-Yuran PPTT anda telah berjaya dideploy!**

