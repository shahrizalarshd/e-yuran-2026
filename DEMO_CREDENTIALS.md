# 🔑 DEMO CREDENTIALS - e-Yuran PPTT

## 📝 Login Credentials

Semua demo accounts menggunakan credentials yang sama untuk **LOCAL** dan **PRODUCTION**.

### **Admin Accounts**

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Super Admin** | `admin@pptt.my` | `PPTTMY@2026` | Full system access |
| **Bendahari** | `bendahari@pptt.my` | `PPTTMY@2026` | Financial management |
| **Auditor** | `auditor@pptt.my` | `PPTTMY@2026` | Read-only access, reports |

### **Resident Account**

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Penduduk** | `penduduk@pptt.my` | `PPTTMY@2026` | Resident portal |

---

## 🌐 Access URLs

### **Production**
```
URL: https://eyuran.pptt.my
Status: Live
```

### **Local Development**
```
URL: http://localhost:8000
Command: php artisan serve
```

---

## 📊 Seeding Data

### **Full Demo Data (with 2023, 2024, 2025 bills)**
```bash
php artisan migrate:fresh --seed --seeder=DemoDataSeeder
```

### **Basic Setup Only**
```bash
php artisan migrate:fresh --seed
```

---

## 🔒 Security Notes

1. **Production Password**: `PPTTMY@2026` adalah untuk demo/testing sahaja
2. **Change in Production**: Tukar password untuk admin accounts sebelum go-live
3. **Password Hash**: Laravel bcrypt dengan cost factor 12
4. **Session**: 2 hours timeout
5. **Email Verified**: Semua demo accounts sudah verified

---

## 🗄️ Database Access

### **Production Database**
```bash
# Via SSH Tunnel (TablePlus/Sequel Ace)
Host: 127.0.0.1
Port: 3307
Database: e_yuran_pptt
Username: eyuran
Password: eIFiAvotwih2uEsNg4cQ

# SSH Tunnel Command
ssh -i ~/.ssh/id_rsa_tableplus -L 3307:127.0.0.1:3306 -N root@206.189.43.187 &
```

### **Local Database**
```bash
# SQLite (default)
Database: database/database.sqlite

# No password required for local
```

---

## 📋 Demo Data Overview

Bila run `DemoDataSeeder`, sistem akan create:

- ✅ 3 Admin users (Super Admin, Bendahari, Auditor)
- ✅ 20 Resident users dengan rumah
- ✅ 20 Active houses (billable)
- ✅ Bills untuk tahun 2023, 2024, 2025
- ✅ Payment records (mixed status)
- ✅ System notifications
- ✅ Audit logs

---

## 🧪 Testing Scenarios

### **Test Super Admin Login**
```
Email: admin@pptt.my
Password: PPTTMY@2026
Expected: Dashboard dengan full menu access
```

### **Test Resident Login**
```
Email: penduduk@pptt.my
Password: PPTTMY@2026
Expected: Resident dashboard dengan bills & payment history
```

### **Test Bendahari Login**
```
Email: bendahari@pptt.my
Password: PPTTMY@2026
Expected: Financial reports & payment management
```

---

## 🔄 Reset Demo Data

### **Local**
```bash
php artisan migrate:fresh --seed --seeder=DemoDataSeeder
```

### **Production** (Hati-hati!)
```bash
ssh root@206.189.43.187
cd /opt/eyuran
docker exec eyuran-app php artisan migrate:fresh --seed --seeder=DemoDataSeeder
```

---

## 📞 Support

Untuk sebarang isu dengan demo accounts atau data seeding:
1. Check migration status: `php artisan migrate:status`
2. Check database connection: `php artisan tinker`
3. View logs: `tail -f storage/logs/laravel.log`

---

**Last Updated**: December 29, 2025
**Version**: 1.0


