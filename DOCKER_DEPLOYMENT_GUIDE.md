# 🐳 PANDUAN DEPLOYMENT DOCKER
## E-Yuran PPTT - DigitalOcean + GitHub Actions

---

## 📋 OVERVIEW

```
┌────────────────────────────────────────────────────────────────┐
│                      DEPLOYMENT FLOW                           │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│   Developer                                                    │
│      │                                                         │
│      ▼                                                         │
│   ┌─────────────┐                                              │
│   │ Push ke     │                                              │
│   │ GitHub      │                                              │
│   └──────┬──────┘                                              │
│          │                                                     │
│          ▼                                                     │
│   ┌─────────────────────────────────────────────┐              │
│   │           GITHUB ACTIONS                    │              │
│   │                                             │              │
│   │  1. Run Tests (PHP 8.2)                     │              │
│   │  2. Build Docker Image                      │              │
│   │  3. Push ke GHCR (GitHub Container Registry)│              │
│   │  4. SSH ke DigitalOcean                     │              │
│   │  5. Pull & Deploy Container                 │              │
│   │  6. Run Migrations                          │              │
│   │  7. Health Check                            │              │
│   │  8. Notify via Telegram                     │              │
│   └──────────────────────┬──────────────────────┘              │
│                          │                                     │
│                          ▼                                     │
│   ┌─────────────────────────────────────────────┐              │
│   │         DIGITALOCEAN DROPLET               │              │
│   │         ┌─────────────────────────────────┐ │              │
│   │         │     Docker Compose              │ │              │
│   │         │  ┌─────┐ ┌─────┐ ┌─────┐       │ │              │
│   │         │  │ App │ │MySQL│ │Redis│       │ │              │
│   │         │  └─────┘ └─────┘ └─────┘       │ │              │
│   │         └─────────────────────────────────┘ │              │
│   └─────────────────────────────────────────────┘              │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

## 🎯 TAHAP 1: SETUP DIGITALOCEAN DROPLET

### 1.1 Buat Droplet Baru

1. Login ke https://cloud.digitalocean.com
2. **Create → Droplets**

**Configuration:**
```
Distribution: Ubuntu 22.04 LTS x64
Plan: Basic ($12/month)
Size: 2GB RAM / 1 CPU / 50GB SSD
Datacenter: Singapore (SGP1)
Authentication: SSH Keys
Hostname: eyuran-production
```

### 1.2 Setup Server

SSH ke droplet dan jalankan script setup:

```bash
# SSH sebagai root
ssh root@YOUR_DROPLET_IP

# Download dan jalankan setup script
curl -sSL https://raw.githubusercontent.com/YOUR_USERNAME/e-yuran-2026/main/docker/server-setup.sh | bash
```

**Atau manual:**

```bash
# Update system
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com | bash

# Create deployer user
adduser --disabled-password --gecos "" deployer
usermod -aG docker deployer
usermod -aG sudo deployer

# Setup SSH for deployer
mkdir -p /home/deployer/.ssh
cp /root/.ssh/authorized_keys /home/deployer/.ssh/
chown -R deployer:deployer /home/deployer/.ssh

# Create project directory
mkdir -p /opt/eyuran
chown deployer:deployer /opt/eyuran

# Setup firewall
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
```

---

## 🎯 TAHAP 2: SETUP GITHUB REPOSITORY

### 2.1 Enable GitHub Container Registry

1. Pergi ke **GitHub → Settings → Developer settings → Personal access tokens**
2. Generate token dengan scope: `write:packages`, `read:packages`, `delete:packages`

### 2.2 Add Repository Secrets

Pergi ke **Repository → Settings → Secrets and variables → Actions**

| Secret Name | Value | Keterangan |
|-------------|-------|------------|
| `DO_HOST` | `YOUR_DROPLET_IP` | IP address droplet |
| `DO_USERNAME` | `deployer` | SSH username |
| `DO_SSH_KEY` | `PRIVATE_KEY` | SSH private key |
| `DO_PORT` | `22` | SSH port |
| `TELEGRAM_BOT_TOKEN` | `your_token` | Optional |
| `TELEGRAM_CHAT_ID` | `your_chat_id` | Optional |

### 2.3 Add Repository Variables

Pergi ke **Repository → Settings → Secrets and variables → Actions → Variables**

| Variable Name | Value |
|---------------|-------|
| `DOMAIN_NAME` | `eyuran.yourdomain.com` |

**Cara dapat SSH private key:**
```bash
# Di local machine
cat ~/.ssh/id_rsa
# Copy SELURUH output (termasuk BEGIN/END lines)
```

---

## 🎯 TAHAP 3: SETUP PRODUCTION ENVIRONMENT

### 3.1 Copy Files ke Server

```bash
# SSH sebagai deployer
ssh deployer@YOUR_DROPLET_IP

cd /opt/eyuran

# Copy docker-compose.prod.yml dari repo
# Atau buat manual:
nano docker-compose.prod.yml
# Paste content dari docker-compose.prod.yml
```

### 3.2 Create Environment File

```bash
nano /opt/eyuran/.env
```

**Paste dan edit:**
```env
# Application
APP_NAME="e-Yuran PPTT"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://eyuran.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=e_yuran_pptt
DB_USERNAME=eyuran
DB_PASSWORD=YOUR_STRONG_PASSWORD
DB_ROOT_PASSWORD=YOUR_ROOT_PASSWORD

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# ToyyibPay
TOYYIBPAY_SECRET_KEY=YOUR_PRODUCTION_KEY
TOYYIBPAY_CATEGORY_CODE=YOUR_CATEGORY
TOYYIBPAY_SANDBOX=false

# Docker
GITHUB_REPOSITORY=your-username/e-yuran-2026
IMAGE_TAG=latest
```

### 3.3 Generate App Key

```bash
# Generate key locally
php artisan key:generate --show

# Copy output dan update APP_KEY dalam .env
```

---

## 🎯 TAHAP 4: FIRST DEPLOYMENT (MANUAL)

### 4.1 Login ke GHCR

```bash
ssh deployer@YOUR_DROPLET_IP
cd /opt/eyuran

# Login ke GitHub Container Registry
echo "YOUR_GITHUB_TOKEN" | docker login ghcr.io -u YOUR_USERNAME --password-stdin
```

### 4.2 Start Services

```bash
# Pull images
docker compose -f docker-compose.prod.yml pull

# Start semua services
docker compose -f docker-compose.prod.yml up -d

# Check status
docker compose -f docker-compose.prod.yml ps

# View logs
docker compose -f docker-compose.prod.yml logs -f
```

### 4.3 Run Migrations

```bash
# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Import legacy data (FIRST TIME ONLY)
docker compose -f docker-compose.prod.yml exec app php artisan import:legacy-data --force
```

---

## 🎯 TAHAP 5: SETUP SSL (Let's Encrypt)

### 5.1 Update Nginx Config

Edit `/opt/eyuran/docker/nginx-proxy.conf`:
- Tukar `eyuran.example.com` ke domain sebenar anda

### 5.2 Get SSL Certificate

```bash
# Initial certificate (HTTP mode)
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    -d eyuran.yourdomain.com \
    --email your@email.com \
    --agree-tos \
    --no-eff-email

# Restart nginx
docker compose -f docker-compose.prod.yml restart nginx
```

---

## 🎯 TAHAP 6: SETUP DNS (Serverfreak)

### 6.1 Login ke Serverfreak

1. Login ke panel Serverfreak
2. Pergi ke **DNS Management**

### 6.2 Add DNS Records

| Type | Host | Value | TTL |
|------|------|-------|-----|
| A | @ | YOUR_DROPLET_IP | 300 |
| A | www | YOUR_DROPLET_IP | 300 |

### 6.3 Verify DNS

```bash
# Tunggu 5-30 minit, kemudian test
ping eyuran.yourdomain.com
# Should return your droplet IP
```

---

## 🎯 TAHAP 7: AUTOMATIC DEPLOYMENT

Selepas setup selesai, setiap push ke `main` branch akan:

1. ✅ Run tests
2. ✅ Build Docker image
3. ✅ Push ke GHCR
4. ✅ Deploy ke server
5. ✅ Run migrations
6. ✅ Notify via Telegram

**Test deployment:**
```bash
# Local machine
git add .
git commit -m "test: docker deployment"
git push origin main

# Check GitHub Actions
# Repository → Actions → Watch the workflow
```

---

## 📊 MONITORING & MAINTENANCE

### View Logs

```bash
ssh deployer@YOUR_DROPLET_IP
cd /opt/eyuran

# All logs
docker compose -f docker-compose.prod.yml logs -f

# App logs only
docker compose -f docker-compose.prod.yml logs -f app

# MySQL logs
docker compose -f docker-compose.prod.yml logs -f mysql
```

### Health Check

```bash
# Check all containers
docker compose -f docker-compose.prod.yml ps

# Health endpoint
curl http://localhost/health
```

### Restart Services

```bash
# Restart all
docker compose -f docker-compose.prod.yml restart

# Restart specific service
docker compose -f docker-compose.prod.yml restart app
```

### Database Backup

```bash
# Manual backup
docker compose -f docker-compose.prod.yml exec mysql \
    mysqldump -u root -p e_yuran_pptt > backup_$(date +%Y%m%d).sql

# Restore
docker compose -f docker-compose.prod.yml exec -T mysql \
    mysql -u root -p e_yuran_pptt < backup.sql
```

---

## 🔧 TROUBLESHOOTING

### Container Won't Start

```bash
# Check logs
docker compose -f docker-compose.prod.yml logs app

# Check container status
docker compose -f docker-compose.prod.yml ps -a

# Recreate containers
docker compose -f docker-compose.prod.yml up -d --force-recreate
```

### Database Connection Failed

```bash
# Check MySQL status
docker compose -f docker-compose.prod.yml exec mysql mysqladmin ping -p

# Check environment
docker compose -f docker-compose.prod.yml exec app printenv | grep DB_
```

### Permission Errors

```bash
# Fix storage permissions
docker compose -f docker-compose.prod.yml exec app \
    chown -R www-data:www-data /var/www/html/storage
```

### Out of Disk Space

```bash
# Clean up Docker
docker system prune -af --volumes

# Check disk usage
df -h
```

---

## 📋 QUICK REFERENCE

```bash
# SSH ke server
ssh deployer@YOUR_DROPLET_IP

# Navigate to project
cd /opt/eyuran

# Start services
docker compose -f docker-compose.prod.yml up -d

# Stop services
docker compose -f docker-compose.prod.yml down

# View logs
docker compose -f docker-compose.prod.yml logs -f

# Run artisan command
docker compose -f docker-compose.prod.yml exec app php artisan [command]

# Database shell
docker compose -f docker-compose.prod.yml exec mysql mysql -u eyuran -p

# Restart specific container
docker compose -f docker-compose.prod.yml restart [app|mysql|redis|nginx]
```

---

## ✅ CHECKLIST

### Pre-Deployment
- [ ] DigitalOcean droplet created
- [ ] Docker installed on server
- [ ] SSH access configured
- [ ] Domain DNS configured
- [ ] GitHub secrets configured

### First Deployment
- [ ] docker-compose.prod.yml on server
- [ ] .env configured
- [ ] Containers running
- [ ] Migrations complete
- [ ] SSL certificate installed

### Post-Deployment
- [ ] Website accessible via HTTPS
- [ ] Login working
- [ ] ToyyibPay configured
- [ ] Telegram notifications (optional)
- [ ] Backup scheduled

---

## 🎉 SELESAI!

Sistem anda sekarang:
- ✅ **Containerized** - Consistent environment
- ✅ **Automated** - Push to deploy
- ✅ **Scalable** - Easy to scale
- ✅ **Secured** - SSL + Firewall
- ✅ **Monitored** - Health checks

**Happy Deploying!** 🚀

