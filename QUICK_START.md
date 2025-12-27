# ⚡ QUICK START GUIDE
## e-Yuran PPTT - Taman Tropika Kajang

---

## 🎯 UNTUK DEVELOPMENT

### Setup Pertama Kali

```bash
# 1. Clone repository
git clone <your-repo-url>
cd e-yuran-2026

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
# Edit .env - configure database
php artisan migrate

# 5. Import legacy data (optional untuk dev)
php artisan import:legacy-data --dry-run
php artisan import:legacy-data

# 6. Build assets
npm run dev

# 7. Run tests
php artisan test

# 8. Start server
php artisan serve
```

**Access:** http://localhost:8000

---

## 🚀 UNTUK PRODUCTION (DIGITALOCEAN)

### Setup Server (Pertama Kali)

1. **Create DigitalOcean Droplet**
   - Ubuntu 22.04 LTS
   - 2GB RAM minimum
   - Singapore datacenter

2. **Setup Server**
   ```bash
   # Lihat: CICD_GUIDE.md - Section 2
   # Install LEMP stack
   # Install Composer, Node, Git
   # Setup firewall
   ```

3. **Deploy Application**
   ```bash
   # Lihat: CICD_GUIDE.md - Section 3-4
   # Clone repository
   # Setup .env
   # Install dependencies
   # Import legacy data
   # Configure Nginx
   # Setup SSL
   ```

4. **Setup GitHub Actions**
   ```bash
   # Lihat: CICD_GUIDE.md - Section 5
   # Add GitHub Secrets
   # Push to main branch = auto deploy!
   ```

### Deployment Automation

**Auto Deploy:**
```bash
git add .
git commit -m "Your changes"
git push origin main
# GitHub Actions will auto-deploy ✅
```

**Manual Deploy:**
```bash
ssh deployer@YOUR_SERVER_IP
cd /var/www/e-yuran
./deploy.sh
```

---

## 📊 IMPORT LEGACY DATA

### Preview Data (Dry Run)
```bash
php artisan import:legacy-data --dry-run
```

### Import Sebenar
```bash
php artisan import:legacy-data --force
```

### Verify Import
```bash
php artisan verify:legacy-data --detailed
```

**Expected Results:**
- 85 houses
- 9,180 bills (2017-2025)
- ~6,420 payments
- 85 membership fees

---

## 🧪 TESTING

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Production readiness
php artisan test --filter=ProductionReadinessTest

# Bill tests
php artisan test --filter=BillTest

# Payment tests
php artisan test --filter=PaymentTest
```

### Check Coverage
```bash
php artisan test --coverage --min=80
```

---

## 🔍 MONITORING & LOGS

### View Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Worker logs (production)
tail -f storage/logs/worker.log
```

### Check Status
```bash
# Application info
php artisan about

# Queue status
php artisan queue:work --once

# Database status
php artisan migrate:status
```

---

## 🛠️ COMMON TASKS

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Database
```bash
# Fresh migration (WARNING: deletes data!)
php artisan migrate:fresh

# Rollback
php artisan migrate:rollback

# Check status
php artisan migrate:status
```

### Assets
```bash
# Development
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

---

## 👤 CREATE ADMIN USER

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@pptt.com',
    'password' => Hash::make('SecurePassword123!'),
    'role' => 'super_admin',
    'language_preference' => 'bm',
    'is_active' => true,
    'email_verified_at' => now(),
]);
```

---

## 📁 FILE STRUCTURE

```
e-yuran-2026/
├── app/
│   ├── Console/Commands/
│   │   ├── ImportLegacyData.php      # Import Excel data
│   │   └── VerifyLegacyData.php      # Verify import
│   ├── Http/Controllers/
│   │   ├── Admin/                     # Admin controllers
│   │   └── Resident/                  # Resident controllers
│   ├── Models/                        # Eloquent models
│   └── Services/
│       ├── BillingService.php
│       └── ToyyibPayService.php
├── database/
│   ├── migrations/                    # Database migrations
│   └── seeders/                       # Database seeders
├── resources/
│   ├── views/                         # Blade templates
│   ├── css/
│   └── js/
├── tests/
│   ├── Feature/                       # Feature tests
│   │   └── ProductionReadinessTest.php  # 88 production tests
│   └── Unit/                          # Unit tests
├── .github/workflows/
│   ├── deploy.yml                     # Auto-deployment
│   └── tests.yml                      # Auto-testing
├── deploy.sh                          # Manual deployment script
├── DEPLOYMENT_GUIDE.md                # Full deployment guide
├── CICD_GUIDE.md                      # GitHub Actions + DO guide
├── LEGACY_DATA_README.md              # Legacy data guide
└── QUICK_START.md                     # This file
```

---

## 🔐 ENVIRONMENT VARIABLES

### Required (.env)
```env
APP_URL=https://your-domain.com
DB_DATABASE=e_yuran_pptt
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
TOYYIBPAY_SECRET_KEY=your_key
TOYYIBPAY_CATEGORY_CODE=your_code
```

### Optional
```env
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id
TELEGRAM_ERROR_NOTIFICATION=true
```

---

## 📞 HELP & DOCUMENTATION

| Topic | File |
|-------|------|
| **System Specification** | `SYSTEM_SPEC_TAMAN_TROPIKA_KAJANG.md` |
| **Deployment Guide** | `DEPLOYMENT_GUIDE.md` |
| **CI/CD Setup** | `CICD_GUIDE.md` |
| **Legacy Data** | `LEGACY_DATA_README.md` |
| **Quick Reference** | `QUICK_START.md` (this file) |

---

## 🆘 TROUBLESHOOTING

### Problem: Tests failing
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Check database
php artisan migrate:status

# Re-run tests
php artisan test
```

### Problem: Permission denied
```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Problem: 500 Error
```bash
# Check logs
tail -50 storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan view:clear

# Check .env
cat .env | grep APP_
```

### Problem: Assets not loading
```bash
# Rebuild assets
npm run build

# Clear browser cache
# Check public/build/ folder exists
```

---

## ✅ PRE-DEPLOYMENT CHECKLIST

### Local Testing
- [ ] All tests passing (476 tests)
- [ ] No linter errors
- [ ] Assets built successfully
- [ ] .env configured
- [ ] Legacy data imported (if needed)

### Server Setup
- [ ] Server provisioned
- [ ] LEMP stack installed
- [ ] Database created
- [ ] Domain configured
- [ ] SSL installed
- [ ] GitHub Actions secrets added

### First Deployment
- [ ] Code deployed
- [ ] .env production configured
- [ ] Dependencies installed
- [ ] Database migrated
- [ ] Legacy data imported
- [ ] Admin user created
- [ ] ToyyibPay configured

### Go Live
- [ ] Test admin login
- [ ] Test resident flow
- [ ] Test payment gateway
- [ ] Check logs for errors
- [ ] Backup configured
- [ ] Monitoring setup

---

## 🎉 READY TO GO!

**Development:** `php artisan serve`
**Production:** `git push origin main` (auto-deploy)
**Documentation:** All guides available in root folder

**Need Help?** Check the respective documentation files above.

---

**Happy Coding!** 🚀



