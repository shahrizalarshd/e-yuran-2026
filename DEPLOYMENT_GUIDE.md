# 📘 PANDUAN DEPLOYMENT PRODUCTION
## Sistem e-Yuran Taman Tropika Kajang

---

## 📋 ISI KANDUNGAN

1. [Prasyarat](#prasyarat)
2. [Setup Environment](#setup-environment)
3. [Import Data Legacy](#import-data-legacy)
4. [Testing Sebelum Deploy](#testing-sebelum-deploy)
5. [Deployment ke Production](#deployment-ke-production)
6. [Post-Deployment Checklist](#post-deployment-checklist)
7. [Backup & Recovery](#backup--recovery)
8. [Troubleshooting](#troubleshooting)

---

## 1. PRASYARAT

### Server Requirements
- PHP >= 8.2
- Composer
- MySQL 8.0+ / PostgreSQL / SQLite
- Node.js & NPM (untuk Vite)
- Web Server (Apache/Nginx)

### File Yang Diperlukan
Pastikan fail Excel legacy data berada dalam root folder projek:
- ✅ `Fail Yuran Tahunan dan Daftar Keahlian PPTT - sent to Marwelies 2 Sept 2022.xlsx`
- ✅ `Rekod Bayaran Yuran 2017-2024.xlsx`
- ✅ `Penyata Yuran 2024.xlsx`
- ✅ `Penyata Yuran 2025.xlsx`

---

## 2. SETUP ENVIRONMENT

### A. Clone & Install Dependencies

```bash
# Clone repository
git clone <repository-url>
cd e-yuran-2026

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install

# Build assets
npm run build
```

### B. Setup .env File

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### C. Konfigurasi Database (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_yuran_pptt
DB_USERNAME=root
DB_PASSWORD=your_password
```

### D. Konfigurasi ToyyibPay

```env
TOYYIBPAY_SECRET_KEY=your_secret_key
TOYYIBPAY_CATEGORY_CODE=your_category_code
TOYYIBPAY_SANDBOX=false
```

### E. Migrate Database

```bash
php artisan migrate
```

---

## 3. IMPORT DATA LEGACY

### ⚠️ PENTING: Import SEBELUM User Mula Daftar

Data legacy **MESTI** di-import **SEBELUM** sistem dibuka kepada pengguna. Ini kerana:
- Import akan clear semua data sedia ada (kecuali users)
- Bil dan payment history dari 2017-2025 akan dicipta
- House master list akan dicipta

### A. Dry Run (Pratonton Sahaja)

```bash
php artisan import:legacy-data --dry-run
```

Output contoh:
```
==============================================
  LEGACY DATA IMPORT - Taman Tropika Kajang
==============================================

🔍 DRY RUN MODE - No changes will be made

📂 Loading Excel files...
   Found 85 houses in valid streets (Jalan 2-5)

📊 IMPORT SUMMARY
─────────────────────────────────────────────
+---------------------------+----------+----------------+
| Item                      | Count    | Amount         |
+---------------------------+----------+----------------+
| Houses to import          | 85       | -              |
| Total bills (2017-2025)   | 9,180    | RM 91,800.00   |
| Paid bills                | 6,420    | RM 64,200.00   |
| Unpaid bills              | 2,760    | RM 27,600.00   |
| Membership fees (paid)    | 72       | RM 1,440.00    |
| Membership fees (unpaid)  | 13       | RM 260.00      |
+---------------------------+----------+----------------+

✅ Dry run completed. No changes made.
```

### B. Import Sebenar

```bash
# Import dengan confirmasi
php artisan import:legacy-data

# Import tanpa confirmasi (untuk automation)
php artisan import:legacy-data --force
```

### C. Options Available

```bash
# Skip importing houses (jika house sudah ada)
php artisan import:legacy-data --skip-houses

# Skip importing bills
php artisan import:legacy-data --skip-bills

# Skip importing membership fees
php artisan import:legacy-data --skip-membership
```

### D. Verify Import

```bash
# Check counts
php artisan tinker
>>> DB::table('houses')->count();
>>> DB::table('bills')->count();
>>> DB::table('payments')->count();
>>> DB::table('membership_fees')->count();
```

---

## 4. TESTING SEBELUM DEPLOY

### A. Run All Tests

```bash
php artisan test
```

Expected output:
```
Tests:    476 passed (783 assertions)
Duration: 6.70s
```

### B. Run Production Readiness Test

```bash
php artisan test --filter=ProductionReadinessTest
```

Expected output:
```
Tests:    88 passed (189 assertions)
Duration: 2.06s
```

### C. Manual Testing Checklist

- [ ] Admin boleh login
- [ ] Dashboard admin papar data yang betul
- [ ] Bil legacy kelihatan dengan betul
- [ ] Resident boleh register (selepas approved)
- [ ] Payment flow berfungsi
- [ ] ToyyibPay callback working

---

## 5. DEPLOYMENT KE PRODUCTION

### A. Setup Production Server

```bash
# Set correct permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### B. Setup Queue Worker (Optional)

```bash
# Install supervisor
sudo apt install supervisor

# Create config: /etc/supervisor/conf.d/e-yuran-worker.conf
[program:e-yuran-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log

# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start e-yuran-worker:*
```

### C. Setup Cron Jobs

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. POST-DEPLOYMENT CHECKLIST

### A. Create Super Admin Account

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@pptt.com',
    'password' => Hash::make('SecurePassword123!'),
    'role' => 'super_admin',
    'language_preference' => 'bm',
    'is_active' => true,
    'email_verified_at' => now(),
]);
```

### B. Configure System Settings

Login sebagai Super Admin dan configure:

1. **Settings → ToyyibPay**
   - Secret Key
   - Category Code
   - Sandbox Mode: OFF

2. **Settings → Telegram** (Optional)
   - Bot Token
   - Chat ID for error notifications

### C. Verify Legacy Data Display

- [ ] Admin Dashboard: Check statistics
- [ ] Bills List: Verify 2017-2025 bills
- [ ] Payment History: Check legacy payments
- [ ] Membership Fees: Verify legacy memberships

### D. Test Complete User Flow

1. User register akaun baru
2. Admin approve user
3. User boleh lihat bil rumah (termasuk legacy)
4. User boleh buat bayaran
5. ToyyibPay callback update status

---

## 7. BACKUP & RECOVERY

### A. Database Backup

```bash
# MySQL backup
mysqldump -u root -p e_yuran_pptt > backup_$(date +%Y%m%d).sql

# Compress
gzip backup_$(date +%Y%m%d).sql
```

### B. Automated Daily Backup

```bash
# Create backup script: /usr/local/bin/backup-eyuran.sh
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/eyuran"
DB_NAME="e_yuran_pptt"

# Create backup
mysqldump -u root -p"$DB_PASSWORD" $DB_NAME | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Keep last 30 days only
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

# Add to crontab
# 0 2 * * * /usr/local/bin/backup-eyuran.sh
```

### C. Restore from Backup

```bash
# Decompress
gunzip backup_20250116.sql.gz

# Restore
mysql -u root -p e_yuran_pptt < backup_20250116.sql
```

---

## 8. TROUBLESHOOTING

### Issue: Import Legacy Data Gagal

**Gejala:** Error semasa import data

**Penyelesaian:**
```bash
# 1. Check fail Excel ada dan betul
ls -lh *.xlsx

# 2. Clear cache
php artisan cache:clear
php artisan config:clear

# 3. Check database connection
php artisan migrate:status

# 4. Run dengan dry-run untuk debug
php artisan import:legacy-data --dry-run
```

### Issue: Unique Constraint Violation

**Gejala:** Error "UNIQUE constraint failed" semasa import

**Penyelesaian:**
```bash
# Clear semua data dan import semula
php artisan migrate:fresh
php artisan import:legacy-data --force
```

### Issue: Bills Tidak Kelihatan

**Gejala:** User tidak nampak bills selepas login

**Penyelesaian:**
```bash
# Check house membership status
php artisan tinker
>>> $house = App\Models\House::find(1);
>>> $house->is_member;  // Mesti TRUE
>>> $house->activeMemberOccupancy();  // Mesti ada
```

### Issue: Payment Callback Tidak Berfungsi

**Gejala:** Status payment tidak update selepas bayar

**Penyelesaian:**
1. Check ToyyibPay webhook URL configured
2. Check return URL accessible dari internet
3. Check logs: `storage/logs/laravel.log`

---

## 9. MAINTENANCE MODE

### Enable Maintenance Mode
```bash
php artisan down --message="System maintenance in progress" --retry=60
```

### Disable Maintenance Mode
```bash
php artisan up
```

### Allow Specific IPs During Maintenance
```bash
php artisan down --allow=203.0.113.1 --allow=203.0.113.2
```

---

## 10. SECURITY CHECKLIST

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] SSL certificate installed (HTTPS)
- [ ] Database credentials secured
- [ ] ToyyibPay secret keys tidak exposed
- [ ] File permissions set correctly
- [ ] `.env` file tidak accessible dari web
- [ ] CSRF protection enabled
- [ ] Session security configured

---

## 📞 SOKONGAN

Untuk bantuan teknikal atau isu deployment, hubungi:
- Developer Team
- Email: support@example.com
- Telegram: @devsupport

---

## 📝 CHANGELOG

### Version 1.0.0 (2026-01-01)
- Initial production release
- Legacy data import (2017-2025)
- Full system testing completed
- 476 automated tests passing

---

**SISTEM SEDIA UNTUK PRODUCTION** ✅
