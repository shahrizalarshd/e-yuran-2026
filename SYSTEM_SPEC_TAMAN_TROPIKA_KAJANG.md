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
- Menyokong data legacy (rekod pembayaran 2017-2024)

---

### 1.2 Scope (LOCKED)
- Fokus kepada **satu taman sahaja**: Taman Tropika Kajang
- Payment gateway: **ToyyibPay sahaja**
- Unit bil: **Rumah (House)**, bukan user
- Keahlian PPTT adalah **SUKARELA** (optional)
- Yuran hanya untuk rumah yang **BERDAFTAR sebagai ahli PPTT**
- Owner boleh join sistem bila-bila masa

---

## 2. MODEL YURAN PPTT (PENTING)

### 2.1 Jenis Yuran (MODEL HIBRID)

| Jenis | Kekerapan | Unit | Sebab |
|-------|-----------|------|-------|
| **Yuran Keahlian** | SEKALI | Per OCCUPANCY | Membership personal, reset bila owner tukar |
| **Yuran Tahunan** | SETIAP TAHUN | Per RUMAH | Maintenance untuk rumah fizikal, inherit bila owner tukar |

---

### 2.2 Yuran Keahlian (Membership Fee) → Per OCCUPANCY

**Konsep Utama:**
- Yuran keahlian adalah bayaran **SEKALI** untuk menjadi ahli PPTT
- Keahlian adalah per **OCCUPANCY** (Owner + Rumah)
- Bila owner bertukar, keahlian **RESET** - owner baru perlu daftar semula
- Bil attach ke `house_occupancy_id`

**Contoh Scenario:**
```
2017: Ahmad beli Rumah No.15
      → Daftar ahli PPTT, bayar RM20
      → Status Keahlian: AHLI ✅

2020: Ahmad jual kepada Abu
      → Keahlian Ahmad TAMAT (occupancy end_date = 2020)
      → Abu ada 2 pilihan:
        A) Daftar ahli → Bayar RM20 → Jadi AHLI
        B) Tak daftar → Kekal BUKAN AHLI → Tiada bil tahunan
```

**Rules:**
- Keahlian adalah SUKARELA
- Setiap owner baru perlu bayar yuran keahlian jika nak jadi ahli
- Keahlian tidak boleh dipindah milik
- Rekod keahlian lama kekal untuk audit trail

---

### 2.3 Yuran Tahunan (Annual Fee) → Per RUMAH

**Konsep Utama:**
- Yuran tahunan adalah untuk **MAINTENANCE RUMAH FIZIKAL**
- Bil attach ke **RUMAH** (`house_id`), bukan occupancy
- Bil dijana setiap tahun untuk rumah yang ada ahli aktif
- Bila owner tukar, bil **INHERIT** kepada owner baru

**Contoh Scenario:**
```
2024 (Januari): Rumah No.15 ada bil RM120 untuk tahun 2024
                Owner: Ahmad (ahli aktif)

2024 (Jun): Ahmad jual kepada Abu
            → Bil 2024 KEKAL attach ke Rumah No.15
            → Jika Ahmad sudah bayar → Abu takde hutang 2024
            → Jika Ahmad belum bayar → Abu nampak tunggakan RM120

2025: Abu daftar ahli, bayar yuran keahlian RM20
      → Bil 2025 dijana untuk Rumah No.15
      → Abu boleh lihat sejarah pembayaran rumah (termasuk masa Ahmad)
```

**Rules:**
- Bil tahunan = per rumah, bukan per orang
- Tunggakan lama visible untuk owner baru
- Owner baru boleh bayar tunggakan (atau negotiate dengan owner lama)
- Hanya generate bil untuk rumah yang ada ahli aktif

**Kenapa Per Rumah?**
1. Maintenance taman adalah untuk rumah fizikal (jalan, lampu, taman)
2. Simple - satu bil per rumah per tahun
3. Senang audit - setiap rumah ada rekod bayaran tahunan
4. Realiti: Tunggakan biasa di-settle masa jual beli atau inherit

---

## 3. USER ROLES & PERMISSIONS

### 3.1 Roles

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

## 4. CORE DOMAIN MODEL

### 4.1 Houses
Rumah ialah **entiti fizikal** dalam sistem. **Yuran tahunan attach di sini.**

**Fields**
- id
- house_no (unique)
- street_name
- status: occupied / vacant
- **is_member** (boolean) - Rumah ini ada ahli aktif? (derived from active occupancy)

**Notes:**
- Rumah adalah unit untuk **yuran tahunan**
- Keahlian adalah di peringkat **Occupancy** (Owner + Rumah)
- Satu rumah boleh ada banyak rekod occupancy (sejarah ownership)
- `is_member` = true jika ada occupancy aktif yang `is_member = true`

**Billing Rule untuk Yuran Tahunan:**
Generate bil tahunan **HANYA JIKA**:
- Ada occupancy aktif dengan `is_member = true`
- Bil attach ke `house_id`

---

### 4.2 Residents
Individu (owner / tenant / family).

**Fields**
- id
- name
- email
- phone
- language_preference (bm / en)

---

### 4.3 House Occupancies (Legal & History)
Jejak owner dan tenant mengikut masa. **Keahlian PPTT di-track di sini.**

**Fields**
- id
- house_id
- resident_id
- role: owner / tenant
- start_date
- end_date (nullable)
- is_payer (boolean)
- **is_member** (boolean) - Adakah ahli PPTT?
- **membership_fee_paid_at** (date, nullable) - Tarikh bayar yuran keahlian
- **membership_fee_amount** (decimal, nullable) - Amaun yuran keahlian

**Rules**
- 1 rumah hanya 1 owner aktif
- 1 rumah hanya 1 tenant aktif
- Owner = default payer
- Owner boleh set tenant sebagai payer
- Perubahan payer → notify admin & tenant + audit log
- **Bila owner bertukar → Occupancy lama end_date = tarikh jual**
- **Owner baru → Occupancy baru, is_member = false (default)**
- **Owner baru perlu daftar & bayar yuran keahlian untuk jadi ahli**

---

### 4.4 House Members (System Access)
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

## 5. USER REGISTRATION & VERIFICATION

### 5.1 Registration Flow
1. User daftar akaun
2. Pilih rumah
3. Pilih relationship
4. Status → `pending`

### 5.2 Verification
Admin approve / reject user.

---

## 6. BILLING MODULE

### 6.1 Fee Configuration

| Jenis Yuran | Konfigurasi |
|-------------|-------------|
| Yuran Keahlian | RM (configurable), bayar sekali |
| Yuran Tahunan | RM (configurable), bayar setiap tahun |

- Perubahan amaun tidak menjejaskan bil lama
- Admin boleh override amaun untuk kes tertentu (audit logged)

---

### 6.2 Billing Engine (MODEL HIBRID)

**Yuran Keahlian (Per Occupancy):**
- Generate bila owner baru pilih untuk daftar ahli
- One-time payment
- Attach ke `house_occupancy_id`
- Reset bila owner bertukar

**Yuran Tahunan (Per Rumah):**
- Generate setiap tahun (Jan) untuk rumah yang ada ahli aktif
- Attach ke `house_id` ⭐
- Bil kekal dengan rumah walaupun owner bertukar
- Owner baru inherit tunggakan (jika ada)

**Membership Bills Table:**
- id
- house_occupancy_id → house_occupancies
- amount
- status: unpaid / paid
- paid_at
- payment_reference

**Annual Bills Table:**
- id
- house_id → houses ⭐
- year
- amount
- status: unpaid / paid
- paid_at
- payment_reference
- paid_by_occupancy_id (siapa yang bayar, untuk audit)

---

## 7. PAYMENT MODULE (TOYYIBPAY)
- Bayar bulan semasa
- Pilih bulan
- Bayar setahun

---

## 8. NOTIFICATION MODULE
- Email
- Internal system notification

---

## 9. AUDIT & GOVERNANCE
- Audit log semua action penting

---

## 10. ERROR MONITORING
- Telegram error notification (Super Admin)

---

## 11. SECURITY
- Role-based access
- Concurrency lock

---

## 12. MULTI-LANGUAGE
- BM & English
- User pilih language

---

## 13. DASHBOARD
- User & Admin dashboard

---

## 14. UI / UX DESIGN (MOBILE-FIRST – WAJIB)

### 14.1 Prinsip Asas UI
- Mobile-first
- Desktop = enhanced view
- Card-based UI
- Button minimum 44px

---

### 14.2 UI Tech Stack
- Blade
- Tailwind CSS
- Alpine.js

---

## 15. USER (RESIDENT) UI

### 15.1 User Dashboard (Mobile)
- Outstanding amount (besar)
- Senarai bil (card)
- Sticky Pay button

---

### 15.2 User Payment Flow
- Pilih bulan / setahun
- Confirmation sebelum ToyyibPay

---

## 16. ADMIN / AJK UI

### 16.1 Admin Dashboard (Mobile)
- Kutipan
- Tunggakan
- Senarai rumah (card)

---

### 16.2 Navigation
- Mobile: slide / bottom menu
- Desktop: sidebar

---

## 17. DESIGN SYSTEM
- Primary color: Hijau
- Status color: Paid / Unpaid / Processing

---

## 18. LEGACY DATA IMPORT (2017-2024)

### 18.1 Sumber Data
Data legacy dari fail Excel:
- `Rekod Bayaran Yuran 2017-2024.xlsx`
- `Fail Yuran Tahunan dan Daftar Keahlian PPTT.xlsx`
- `Penyata Yuran 2024.xlsx`
- `Penyata Yuran 2025.xlsx`

### 18.2 Legacy Payments Table

**Fields:**
- id
- house_no (key untuk matching)
- payment_type: membership / annual
- year (untuk annual, null untuk membership)
- amount
- payment_date
- owner_name (nama dari rekod lama)
- notes
- imported_at
- **linked_to_house_id** (untuk yuran tahunan) ⭐
- **linked_to_occupancy_id** (untuk yuran keahlian) ⭐

### 18.3 Linking Flow (MODEL HIBRID)

```
┌─────────────────────────────────────────────────────────────────┐
│  IMPORT DATA LEGACY (SEBELUM SISTEM LAUNCH)                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Admin import fail Excel                                    │
│                    ▼                                            │
│  2. Sistem parse data:                                         │
│     - Yuran Keahlian → simpan dengan payment_type = 'membership'│
│     - Yuran Tahunan → simpan dengan payment_type = 'annual'    │
│                    ▼                                            │
│  3. Auto-create houses dari senarai house_no                   │
│     - Link yuran tahunan terus ke house_id ⭐                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  USER LEGACY DAFTAR AKAUN (2026)                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. User daftar & pilih rumah (No. 15)                         │
│                    ▼                                            │
│  2. Sistem check legacy_payments WHERE house_no = '15'         │
│                    ▼                                            │
│  3. Papar rekod dijumpai:                                      │
│     ┌───────────────────────────────────────────────────────┐  │
│     │ 📋 REKOD PEMBAYARAN RUMAH NO. 15                      │  │
│     │                                                       │  │
│     │ YURAN KEAHLIAN (akan link ke anda):                  │  │
│     │ ✅ RM20 (2017) - Ahmad bin Ali                        │  │
│     │                                                       │  │
│     │ YURAN TAHUNAN (sudah link ke rumah):                 │  │
│     │ ✅ 2017: RM120 (Paid)                                 │  │
│     │ ✅ 2018: RM120 (Paid)                                 │  │
│     │ ✅ 2019: RM120 (Paid)                                 │  │
│     │ ...                                                   │  │
│     │ ✅ 2024: RM120 (Paid)                                 │  │
│     └───────────────────────────────────────────────────────┘  │
│                    ▼                                            │
│  4. User confirm: "Saya adalah owner sah rumah ini"            │
│                    ▼                                            │
│  5. Admin approve                                               │
│                    ▼                                            │
│  6. Sistem:                                                     │
│     - Create occupancy (is_member = true)                      │
│     - Link yuran keahlian legacy ke occupancy_id               │
│     - Set membership_fee_paid_at dari rekod lama               │
│     - Yuran tahunan sudah sedia link ke rumah                  │
│                    ▼                                            │
│  7. User boleh lihat:                                           │
│     - Sejarah keahlian (untuk occupancy mereka)                │
│     - Sejarah yuran tahunan (untuk rumah, termasuk owner lama) │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 18.4 Rules
- Data legacy adalah **READ-ONLY** selepas import
- Tidak boleh edit rekod legacy (audit compliance)
- Rekod baru (2026+) masuk jadual `membership_bills` dan `annual_bills`
- View payment history:
  - Yuran tahunan → gabung legacy + annual_bills (per rumah)
  - Yuran keahlian → gabung legacy + membership_bills (per occupancy)

---

## 19. DATABASE SCHEMA SUMMARY (MODEL HIBRID)

```
houses
├── id
├── house_no (unique)
├── street_name
├── status (occupied/vacant)
└── is_member (derived: ada occupancy aktif yang is_member?) ⭐

residents
├── id
├── name
├── email
├── phone
└── language_preference

house_occupancies
├── id
├── house_id → houses
├── resident_id → residents
├── role (owner/tenant)
├── start_date
├── end_date (null = aktif)
├── is_payer
├── is_member ⭐ (ahli PPTT?)
├── membership_fee_paid_at ⭐
└── membership_fee_amount ⭐

house_members
├── id
├── house_id → houses
├── resident_id → residents
├── relationship
├── can_view_bills
├── can_pay
└── status

membership_bills ⭐ (YURAN KEAHLIAN - per occupancy)
├── id
├── house_occupancy_id → house_occupancies
├── amount
├── status (unpaid/paid)
├── paid_at
└── payment_reference

annual_bills ⭐ (YURAN TAHUNAN - per rumah)
├── id
├── house_id → houses
├── year
├── amount
├── status (unpaid/paid)
├── paid_at
├── payment_reference
└── paid_by_occupancy_id → house_occupancies (audit trail)

legacy_payments ⭐ (DATA LAMA 2017-2024)
├── id
├── house_no
├── payment_type (membership/annual)
├── year (null untuk membership)
├── amount
├── payment_date
├── owner_name
├── notes
├── imported_at
├── linked_to_house_id → houses (untuk annual) ⭐
└── linked_to_occupancy_id → house_occupancies (untuk membership) ⭐
```

### Schema Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        MODEL HIBRID                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────┐         ┌──────────────────┐         ┌──────────┐    │
│  │ residents│────────▶│ house_occupancies│◀────────│  houses  │    │
│  └──────────┘         └──────────────────┘         └──────────┘    │
│                               │                          │          │
│                               │                          │          │
│                               ▼                          ▼          │
│                      ┌────────────────┐         ┌─────────────┐    │
│                      │membership_bills│         │ annual_bills│    │
│                      │ (per occupancy)│         │ (per rumah) │    │
│                      └────────────────┘         └─────────────┘    │
│                                                                     │
│  YURAN KEAHLIAN                      YURAN TAHUNAN                 │
│  → Reset bila owner tukar            → Inherit bila owner tukar    │
│  → Personal membership               → Untuk rumah fizikal         │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## FINAL NOTE
UI dan sistem **mesti ikut spesifikasi ini sepenuhnya**.
