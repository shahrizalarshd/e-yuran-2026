# 📊 PANDUAN DATA LEGACY
## Sistem e-Yuran Taman Tropika Kajang

---

## 🎯 RINGKASAN

Data legacy adalah **rekod pembayaran yuran dari tahun 2017 hingga 2025** yang perlu diimport ke dalam sistem sebelum dibuka kepada pengguna.

### ⚠️ PENTING
**Import data legacy MESTI dilakukan SEBELUM sistem dibuka untuk public registration!**

---

## 📁 FAIL YANG DIPERLUKAN

Pastikan fail-fail berikut ada dalam folder root projek:

```
e-yuran-2026/
├── Fail Yuran Tahunan dan Daftar Keahlian PPTT - sent to Marwelies 2 Sept 2022.xlsx
├── Rekod Bayaran Yuran 2017-2024.xlsx
├── Penyata Yuran 2024.xlsx
└── Penyata Yuran 2025.xlsx
```

### Kandungan Fail

| Fail | Kegunaan |
|------|----------|
| **Fail Yuran Tahunan...xlsx** | Master list rumah, nama owner, status |
| **Rekod Bayaran...xlsx** | Rekod pembayaran 2017-2024 (12 sheet = 12 tahun) |
| **Penyata Yuran 2024.xlsx** | Update pembayaran 2024 (jika ada) |
| **Penyata Yuran 2025.xlsx** | Pembayaran 2025 |

---

## 🚀 CARA IMPORT

### STEP 1: Dry Run (Pratonton)

**SENTIASA buat dry run dahulu untuk preview:**

```bash
php artisan import:legacy-data --dry-run
```

Output:
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
```

**Semak:**
- ✅ Jumlah rumah betul (expected: ~85 rumah)
- ✅ Jumlah bil masuk akal (85 rumah × 9 tahun × 12 bulan = 9,180 bil)
- ✅ Amaun pembayaran masuk akal

### STEP 2: Import Sebenar

**Jika dry run OK, proceed dengan import:**

```bash
# Dengan confirmasi (selamat)
php artisan import:legacy-data

# Tanpa confirmasi (untuk automation)
php artisan import:legacy-data --force
```

**Proses akan:**
1. ✅ Load semua Excel files
2. ✅ Parse data rumah, bil, dan membership
3. ⚠️  **CLEAR existing data** (kecuali users)
4. ✅ Create fee configurations
5. ✅ Import 85 rumah
6. ✅ Create 9,180 bil (2017-2025)
7. ✅ Create payment records untuk bil yang paid
8. ✅ Import membership fees

**Masa: ~2-3 minit**

### STEP 3: Verify Import

```bash
php artisan verify:legacy-data
```

Output:
```
╔════════════════════════════════════════════════════╗
║   LEGACY DATA VERIFICATION                         ║
║   e-Yuran Taman Tropika Kajang                     ║
╚════════════════════════════════════════════════════╝

📊 OVERALL STATUS
─────────────────────────────────────────────────────
+-----------------------+---------+-----------+
| Item                  | Count   | Status    |
+-----------------------+---------+-----------+
| Houses                | 85      | ✅ OK     |
| Bills (Legacy)        | 9,180   | ✅ OK     |
| Payments (Legacy)     | 6,420   | ✅ OK     |
| Membership Fees       | 85      | ✅ OK     |
+-----------------------+---------+-----------+

🏘️  HOUSES BY STREET
─────────────────────────────────────────────────────
   Jalan Tropika 2: 22 houses
   Jalan Tropika 3: 21 houses
   Jalan Tropika 4: 20 houses
   Jalan Tropika 5: 22 houses

💰 FINANCIAL SUMMARY
─────────────────────────────────────────────────────
+-------------------+-----------------+--------------------+
| Category          | Collected (RM)  | Outstanding (RM)   |
+-------------------+-----------------+--------------------+
| Annual Fees       | 64,200.00       | 27,600.00          |
| Membership Fees   | 1,440.00        | 260.00             |
| TOTAL             | 65,640.00       | 27,860.00          |
+-------------------+-----------------+--------------------+

╔════════════════════════════════════════════════════╗
║   ✅ LEGACY DATA VERIFICATION PASSED               ║
║   System is ready for production                   ║
╚════════════════════════════════════════════════════╝
```

---

## 🔄 BILA PERLU IMPORT SEMULA?

Import semula **HANYA** jika:
1. ❌ Ada kesilapan data dalam Excel
2. ❌ Perlu update rekod lama
3. ❌ Testing/development purposes

**⚠️ WARNING: Import akan DELETE semua data existing!**

---

## 📋 DATA YANG DICIPTA

### A. Houses (Rumah)

```sql
-- 85 rumah dari Jalan Tropika 2, 3, 4, 5
houses:
  - house_no: "1", "2", "3", etc.
  - street_name: "Jalan Tropika 2"
  - is_registered: true
  - is_active: true
  - status: "occupied"
```

### B. Bills (Bil Bulanan)

```sql
-- 9,180 bil (85 houses × 9 years × 12 months)
bills:
  - bill_no: "BIL-202301-00001"
  - bill_year: 2023
  - bill_month: 1-12
  - amount: 10.00
  - status: "paid" / "unpaid"
  - is_legacy: true  ← FLAG untuk legacy data
  - paid_at: "2023-01-28" (jika paid)
```

### C. Payments (Rekod Bayaran)

```sql
-- 6,420 payment records (untuk bil yang paid)
payments:
  - payment_no: "LEG-202301-00001"
  - amount: 10.00
  - status: "success"
  - payment_type: "current_month"
  - is_legacy: true  ← FLAG untuk legacy
  - payment_method: "legacy"
  - legacy_reference: "TnG", "Cash", etc.
```

### D. Membership Fees (Yuran Keahlian)

```sql
-- 85 membership fees (1 per house)
membership_fees:
  - amount: 20.00
  - status: "paid" / "unpaid"
  - paid_at: "2017-01-01" (jika paid)
  - is_legacy: true
  - legacy_owner_name: "Ahmad bin Ali"
  - fee_year: 2017
```

---

## 🎯 KONSEP MODEL HIBRID

### Yuran Tahunan (Annual Bills) → Per RUMAH

```
Rumah No. 15, Jalan Tropika 2
├── 2017: 12 bil (Jan-Dec) → Attach to HOUSE
├── 2018: 12 bil (Jan-Dec) → Attach to HOUSE  
├── 2019: 12 bil (Jan-Dec) → Attach to HOUSE
├── ...
└── 2025: 12 bil (Jan-Dec) → Attach to HOUSE

Bila owner tukar → Bil KEKAL dengan rumah (inherit)
```

### Yuran Keahlian (Membership) → Per OCCUPANCY

```
Rumah No. 15, Jalan Tropika 2
├── Owner Lama (2017-2020): Yuran Keahlian RM20 (PAID)
└── Owner Baru (2021+): Perlu bayar Yuran Keahlian SEMULA

Bila owner tukar → Keahlian RESET
```

---

## 🔍 CARA SEMAK DATA SELEPAS IMPORT

### A. Via Tinker

```bash
php artisan tinker
```

```php
// Check houses
DB::table('houses')->count();  // Expected: 85

// Check bills
DB::table('bills')->where('is_legacy', true)->count();  // Expected: 9,180

// Check payments
DB::table('payments')->where('is_legacy', true)->count();  // Expected: ~6,420

// Check membership fees
DB::table('membership_fees')->count();  // Expected: 85

// Check specific house
$house = App\Models\House::where('house_no', '1')
    ->where('street_name', 'Jalan Tropika 2')
    ->first();
    
$house->bills()->count();  // Should have 108 bills (9 years × 12 months)
```

### B. Via Admin Dashboard

1. Login sebagai Super Admin
2. Dashboard → Check statistics
3. Bills → Filter by year 2017-2025
4. Houses → Check house list

### C. Via Verify Command

```bash
php artisan verify:legacy-data --detailed
```

---

## ❓ TROUBLESHOOTING

### Problem: "File not found"

**Penyelesaian:**
```bash
# Check fail ada
ls -lh *.xlsx

# Expected output:
# -rw-r--r--  Fail Yuran Tahunan...xlsx
# -rw-r--r--  Rekod Bayaran Yuran...xlsx
# -rw-r--r--  Penyata Yuran 2024.xlsx
# -rw-r--r--  Penyata Yuran 2025.xlsx
```

### Problem: "Duplicate entry" atau "UNIQUE constraint failed"

**Sebab:** Data sudah ada dalam database

**Penyelesaian:**
```bash
# Option 1: Fresh import (delete all data)
php artisan migrate:fresh
php artisan import:legacy-data --force

# Option 2: Manual clear
php artisan tinker
>>> DB::table('payment_bill')->delete();
>>> DB::table('payments')->delete();
>>> DB::table('bills')->delete();
>>> DB::table('membership_fees')->delete();
>>> DB::table('houses')->delete();
```

### Problem: "Wrong number of houses imported"

**Expected:** ~85 houses (Jalan Tropika 2, 3, 4, 5 sahaja)

**Penyelesaian:**
1. Check Excel sheet "Rekod register"
2. Verify Column E (Jalan) hanya ada 2, 3, 4, 5
3. Run dry-run untuk preview

### Problem: Import sangat lambat

**Normal:** 2-3 minit untuk 9,180 bil

**Jika lebih 10 minit:**
```bash
# Check database performance
# Check disk space
df -h

# Check memory
free -m

# Optimize database
php artisan optimize
```

---

## 📝 CHECKLIST SEBELUM PRODUCTION

- [ ] Fail Excel sudah ada dan betul
- [ ] Dry run passed tanpa error
- [ ] Import completed successfully
- [ ] Verify command passed
- [ ] Check statistics in admin dashboard
- [ ] Sample check: 3-5 rumah data betul
- [ ] Financial totals masuk akal
- [ ] Backup database before open to public

---

## 🔐 SECURITY NOTE

**Fail Excel mengandungi data sensitif:**
- Nama owner
- Status pembayaran
- Alamat rumah

**Selepas import:**
1. ✅ Data sudah dalam database (encrypted)
2. ✅ Boleh delete fail Excel dari server production
3. ✅ Simpan backup fail Excel di tempat selamat
4. ✅ JANGAN commit fail Excel ke Git

---

## ✅ NEXT STEPS SELEPAS IMPORT

1. **Create Super Admin account**
   ```bash
   php artisan tinker
   >>> $user = App\Models\User::create([...]);
   ```

2. **Configure ToyyibPay settings**
   - Login as Super Admin
   - Settings → ToyyibPay
   - Enter Secret Key & Category Code

3. **Test resident registration flow**
   - User register
   - Admin approve
   - User view bills (including legacy)

4. **Open to public** 🎉

---

**SISTEM SEDIA UNTUK PRODUCTION** ✅

