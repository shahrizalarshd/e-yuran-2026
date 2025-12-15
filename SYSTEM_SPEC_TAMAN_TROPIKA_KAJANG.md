# 📘 SYSTEM SPECIFICATION
## Sistem e-Yuran Perumahan
### Taman Tropika Kajang

---

## 1. SYSTEM OVERVIEW

### 1.1 Objective
Menyediakan sistem kutipan yuran perumahan taman yang:
- Telus
- Audit-ready
- Mesra keluarga (ramai user satu rumah)
- Realistik mengikut operasi perumahan taman di Malaysia

---

### 1.2 Scope (LOCKED)
- Fokus kepada **satu taman sahaja**: Taman Tropika Kajang
- Payment gateway: **ToyyibPay sahaja**
- Unit bil: **Rumah (House)**, bukan user
- Yuran **WAJIB** hanya untuk rumah:
  - Berdaftar (`is_registered = true`)
  - Aktif (`is_active = true`)
- Rumah tidak berdaftar → tiada bil
- Owner boleh join sistem bila-bila masa

---

## 2. USER ROLES & PERMISSIONS

### 2.1 Roles

#### 🟢 Super Admin
- Full system access
- Set ToyyibPay credentials
- Set Telegram error log notification
- Override data (audit logged)

#### 🟡 Treasurer
- View semua bil & pembayaran
- Payment reconciliation
- Generate laporan kewangan
- Tidak boleh edit bil yang sudah `Paid`

#### 🔵 Auditor (Read-Only)
- View bil
- View pembayaran
- View audit log
- Tidak boleh edit data

#### ⚪ Resident (House Member)
- View bil rumah
- Bayar bil (jika `can_pay = true`)
- View payment history

---

## 3. CORE DOMAIN MODEL

### 3.1 Houses
Rumah ialah **akaun utama sistem**.

**Fields**
- id
- house_no
- street_name
- is_registered (boolean)
- is_active (boolean)
- status: occupied / vacant

**Billing Rule**
Generate bil **HANYA JIKA**:
- is_registered = true
- is_active = true

---

### 3.2 Residents
Individu (owner / tenant / family).

**Fields**
- id
- name
- email
- phone
- language_preference (bm / en)

---

### 3.3 House Occupancies (Legal & History)
Jejak owner dan tenant mengikut masa.

**Fields**
- id
- house_id
- resident_id
- role: owner / tenant
- start_date
- end_date (nullable)
- is_payer (boolean)

**Rules**
- 1 rumah hanya 1 owner aktif
- 1 rumah hanya 1 tenant aktif
- Owner = default payer
- Owner boleh set tenant sebagai payer
- Perubahan payer → notify admin & tenant + audit log

---

### 3.4 House Members (System Access)
Untuk ahli keluarga / wakil rumah.

**Fields**
- id
- house_id
- resident_id
- relationship: owner / spouse / child / family / tenant
- can_view_bills (boolean)
- can_pay (boolean)
- status: pending / active / inactive / rejected

---

## 4. USER REGISTRATION & VERIFICATION

### 4.1 Registration Flow
1. User daftar akaun
2. Pilih rumah
3. Pilih relationship
4. Status → `pending`

### 4.2 Verification
Admin approve / reject user.

---

## 5. BILLING MODULE

### 5.1 Fee Configuration
- Tetapkan yuran bulanan
- Perubahan amaun tidak menjejaskan bil lama

---

### 5.2 Billing Engine
- Generate bil bulanan
- Bil attach ke `house_id`

---

## 6. PAYMENT MODULE (TOYYIBPAY)
- Bayar bulan semasa
- Pilih bulan
- Bayar setahun

---

## 7. NOTIFICATION MODULE
- Email
- Internal system notification

---

## 8. AUDIT & GOVERNANCE
- Audit log semua action penting

---

## 9. ERROR MONITORING
- Telegram error notification (Super Admin)

---

## 10. SECURITY
- Role-based access
- Concurrency lock

---

## 11. MULTI-LANGUAGE
- BM & English
- User pilih language

---

## 12. DASHBOARD
- User & Admin dashboard

---

## 13. UI / UX DESIGN (MOBILE-FIRST – WAJIB)

### 13.1 Prinsip Asas UI
- Mobile-first
- Desktop = enhanced view
- Card-based UI
- Button minimum 44px

---

### 13.2 UI Tech Stack
- Blade
- Tailwind CSS
- Alpine.js

---

## 14. USER (RESIDENT) UI

### 14.1 User Dashboard (Mobile)
- Outstanding amount (besar)
- Senarai bil (card)
- Sticky Pay button

---

### 14.2 User Payment Flow
- Pilih bulan / setahun
- Confirmation sebelum ToyyibPay

---

## 15. ADMIN / AJK UI

### 15.1 Admin Dashboard (Mobile)
- Kutipan
- Tunggakan
- Senarai rumah (card)

---

### 15.2 Navigation
- Mobile: slide / bottom menu
- Desktop: sidebar

---

## 16. DESIGN SYSTEM
- Primary color: Hijau
- Status color: Paid / Unpaid / Processing

---

## FINAL NOTE
UI dan sistem **mesti ikut spesifikasi ini sepenuhnya**.
