# 📘 MANUAL SUPER ADMIN
## Sistem e-Yuran PPTT
### Persatuan Penduduk Taman Tropika Kajang

---

**Versi**: 1.0  
**Tarikh Kemaskini**: 30 Disember 2025  
**Untuk**: Super Admin PPTT

---

## 📋 ISI KANDUNGAN

1. [Pengenalan](#1-pengenalan)
2. [Cara Login](#2-cara-login)
3. [Dashboard Super Admin](#3-dashboard-super-admin)
4. [Pengurusan Rumah](#4-pengurusan-rumah)
5. [Pengurusan Penduduk](#5-pengurusan-penduduk)
6. [Pengurusan Bil](#6-pengurusan-bil)
7. [Pengurusan Pembayaran](#7-pengurusan-pembayaran)
8. [Konfigurasi Yuran](#8-konfigurasi-yuran)
9. [Yuran Keahlian](#9-yuran-keahlian)
10. [Tetapan Sistem](#10-tetapan-sistem)
11. [Log Audit](#11-log-audit)
12. [Penyelenggaraan Sistem](#12-penyelenggaraan-sistem)
13. [Soalan Lazim (FAQ)](#13-soalan-lazim-faq)

---

## 1. PENGENALAN

### 1.1 Peranan Super Admin

Sebagai Super Admin, anda mempunyai **akses penuh** kepada semua fungsi sistem:

- ✅ Pengurusan penuh rumah dan penduduk
- ✅ Menjana dan menguruskan bil
- ✅ Melihat dan menguruskan pembayaran
- ✅ Konfigurasi yuran dan tetapan sistem
- ✅ Mengesahkan pendaftaran pengguna
- ✅ Akses log audit
- ✅ Tetapan ToyyibPay dan Telegram

### 1.2 Akses Super Admin

| Fungsi | Boleh Lihat | Boleh Edit |
|--------|-------------|------------|
| Dashboard Analitik | ✅ | - |
| Pengurusan Rumah | ✅ | ✅ |
| Pengurusan Penduduk | ✅ | ✅ |
| Pengurusan Bil | ✅ | ✅ |
| **Jana Bil** | ✅ | ✅ |
| **Padam Bil** | ✅ | ✅ |
| Senarai Pembayaran | ✅ | ❌ |
| Laporan Kewangan | ✅ | - |
| Pengesahan Pengguna | ✅ | ✅ |
| Konfigurasi Yuran | ✅ | ✅ |
| **Tetapan Sistem** | ✅ | ✅ |
| **Log Audit** | ✅ | ❌ |

### 1.3 Maklumat Login

| Maklumat | Nilai |
|----------|-------|
| **URL** | https://eyuran.pptt.my |
| **E-mel** | admin@pptt.my |
| **Kata Laluan** | *(tukar dari default)* |

**⚠️ PENTING:** Tukar kata laluan default selepas login pertama!

---

## 2. CARA LOGIN

### 2.1 Langkah Login

1. Buka pelayar web: **https://eyuran.pptt.my**
2. Klik **"Log Masuk"**
3. Masukkan e-mel: `admin@pptt.my`
4. Masukkan kata laluan
5. Klik **"Log Masuk"**

### 2.2 Keselamatan Akaun

Sebagai Super Admin, pastikan:

- 🔒 Gunakan kata laluan yang kuat (min. 12 aksara)
- 🔒 Jangan kongsi kata laluan
- 🔒 Log keluar selepas selesai
- 🔒 Jangan simpan kata laluan dalam pelayar berkongsi
- 🔒 Tukar kata laluan setiap 3 bulan

---

## 3. DASHBOARD SUPER ADMIN

### 3.1 Statistik Utama

```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ 💰 KUTIPAN   │ │ ⚠️ TERTUNGGAK│ │ 🏠 RUMAH     │ │ ⏰ LEWAT     │
│ RM 15,240    │ │ RM 3,600     │ │ 18/20        │ │ 12           │
│ Jumlah tahun │ │ Belum bayar  │ │ Ahli aktif   │ │ Bil lewat    │
└──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
```

### 3.2 Butang Tindakan Cepat

| Butang | Fungsi |
|--------|--------|
| **➕ Jana Bil** | Menjana bil tahunan |
| **📊 Laporan Tertunggak** | Lihat senarai tunggakan |
| **📈 Laporan Kutipan** | Lihat laporan kewangan |
| **✅ Pengesahan Pengguna** | Sahkan pendaftaran baru |

### 3.3 Analitik Kewangan

- **Kutipan Bulanan**: Carta perbandingan tahun
- **Kadar Kutipan**: Peratusan bil dibayar
- **Status Bil**: Pecahan status bil
- **Kutipan 7 Hari**: Trend kutipan harian

---

## 4. PENGURUSAN RUMAH

### 4.1 Lihat Senarai Rumah

1. Klik **"Rumah"** dari menu
2. Senarai semua rumah dalam taman dipaparkan

### 4.2 Tambah Rumah Baru

1. Klik butang **"➕ Tambah Rumah"**
2. Isi maklumat:
   - **Nombor Rumah**: contoh "15"
   - **Nama Jalan**: contoh "Jalan Tropika 1"
   - **Status**: Diduduki / Kosong
3. Klik **"Simpan"**

### 4.3 Edit Rumah

1. Cari rumah dalam senarai
2. Klik butang **"Edit"**
3. Kemaskini maklumat
4. Klik **"Simpan"**

### 4.4 Butiran Rumah

Klik pada rumah untuk melihat:

| Tab | Kandungan |
|-----|-----------|
| **Maklumat** | Alamat, status, keahlian |
| **Pemilik** | Senarai pemilik/penyewa |
| **Bil** | Sejarah bil rumah |
| **Pembayaran** | Sejarah pembayaran |

### 4.5 Assign Pemilik/Penyewa

Untuk mengaitkan penduduk dengan rumah:

1. Buka butiran rumah
2. Klik **"Assign Pemilik"** atau **"Assign Penyewa"**
3. Pilih penduduk dari senarai (atau cipta baru)
4. Isi maklumat:
   - **Tarikh Mula**: Bila mula tinggal
   - **Adalah Ahli PPTT**: Ya/Tidak
5. Klik **"Simpan"**

### 4.6 Tukar Pemilik

Bila rumah bertukar tangan:

1. Buka butiran rumah
2. Pada pemilik semasa, klik **"Tamatkan"**
3. Masukkan tarikh tamat
4. **Assign Pemilik** baru
5. Tetapkan keahlian untuk pemilik baru

**PENTING tentang Model Yuran:**

| Jenis Yuran | Bila Tukar Pemilik |
|-------------|-------------------|
| **Yuran Keahlian** | Reset - pemilik baru perlu bayar semula |
| **Yuran Tahunan** | Inherit - tunggakan ikut rumah |

---

## 5. PENGURUSAN PENDUDUK

### 5.1 Lihat Senarai Penduduk

1. Klik **"Penduduk"** dari menu
2. Senarai semua penduduk dipaparkan

### 5.2 Penapis Penduduk

| Penapis | Pilihan |
|---------|---------|
| **Status** | Aktif / Menunggu / Ditolak |
| **Rumah** | Pilih rumah tertentu |

### 5.3 Pengesahan Pendaftaran

Bila penduduk baru daftar:

1. Klik **"Penduduk"** → **"Menunggu Pengesahan"**
2. Semak maklumat pendaftar
3. Sahkan dengan pemilik sebenar (jika perlu)
4. Klik **✅ Luluskan** atau **❌ Tolak**

### 5.4 Butiran Penduduk

| Maklumat | Keterangan |
|----------|------------|
| Nama | Nama penuh |
| E-mel | Alamat e-mel |
| Telefon | Nombor telefon |
| Rumah | Rumah yang dikaitkan |
| Hubungan | Owner/Spouse/Child/Tenant |
| Kebenaran | Boleh bayar? Boleh lihat bil? |

### 5.5 Kemaskini Kebenaran

Untuk mengubah kebenaran ahli rumah:

1. Buka butiran penduduk
2. Klik **"Kemaskini Kebenaran"**
3. Tetapkan:
   - **Boleh Lihat Bil**: Ya/Tidak
   - **Boleh Bayar**: Ya/Tidak
4. Klik **"Simpan"**

---

## 6. PENGURUSAN BIL

### 6.1 Lihat Senarai Bil

1. Klik **"Bil"** dari menu
2. Semua bil dipaparkan

### 6.2 Jana Bil Tahunan

**Ini adalah fungsi penting yang hanya Super Admin boleh lakukan.**

#### Langkah Menjana Bil:

1. Klik **"Bil"** → **"Jana Bil"**
2. Pilih **Tahun**: contoh 2026
3. Sistem akan menunjukkan:
   - Bilangan rumah ahli aktif
   - Amaun bil setiap rumah
   - Jumlah keseluruhan

```
┌─────────────────────────────────────────┐
│  JANA BIL TAHUNAN 2026                  │
├─────────────────────────────────────────┤
│  Rumah dengan ahli aktif: 18            │
│  Amaun sebulan: RM 10.00               │
│  Jumlah bil: 18 × 12 = 216 bil         │
│                                         │
│  ⚠️ Pastikan konfigurasi yuran betul    │
│     sebelum menjana bil.                │
│                                         │
│  [ 🔄 JANA BIL 2026 ]                   │
└─────────────────────────────────────────┘
```

4. Klik **"Jana Bil"**
5. Sistem akan menjana bil untuk semua 12 bulan

#### Bila Jana Bil?

- **Awal tahun** (Januari) setiap tahun
- Selepas keputusan AGM tentang amaun yuran
- Pastikan konfigurasi yuran sudah dikemaskini

### 6.3 Edit Bil

1. Cari bil yang ingin diedit
2. Klik butang **"Edit"**
3. Kemaskini:
   - Jumlah (jika perlu pelarasan)
   - Tarikh akhir
   - Nota
4. Klik **"Simpan"**

**⚠️ AMARAN:**
- Bil yang sudah dibayar TIDAK patut diedit
- Semua perubahan direkod dalam Log Audit

### 6.4 Padam Bil

**Gunakan dengan BERHATI-HATI!**

1. Buka butiran bil
2. Klik **"Padam"**
3. Sahkan tindakan
4. Bil akan dipadam

**Bila perlu padam bil:**
- Bil dijana tersilap
- Rumah tidak lagi ahli

**⚠️ PENTING:** Bil yang sudah dibayar TIDAK boleh dipadam!

### 6.5 Hantar Peringatan

1. Klik **"Bil"** → **"Hantar Peringatan"**
2. Pilih rumah atau hantar kepada semua
3. Klik **"Hantar"**
4. E-mel peringatan akan dihantar kepada penduduk

---

## 7. PENGURUSAN PEMBAYARAN

### 7.1 Lihat Senarai Pembayaran

1. Klik **"Pembayaran"** dari menu
2. Semua pembayaran dipaparkan

### 7.2 Butiran Pembayaran

| Maklumat | Keterangan |
|----------|------------|
| No. Pembayaran | Nombor rujukan |
| Tarikh | Tarikh transaksi |
| Rumah | Alamat rumah |
| Jumlah | Amaun bayaran |
| Status | Berjaya/Gagal |
| Rujukan | Rujukan ToyyibPay |
| Bil | Senarai bil yang dibayar |

### 7.3 Laporan Kutipan

1. Klik **"Pembayaran"** → **"Laporan Kutipan"**
2. Pilih tahun dan bulan
3. Lihat ringkasan kutipan

### 7.4 Rekonsiliasi

1. Klik **"Pembayaran"** → **"Rekonsiliasi"**
2. Bandingkan dengan penyata ToyyibPay
3. Pastikan semua pembayaran sepadan

---

## 8. KONFIGURASI YURAN

### 8.1 Yuran Tahunan

#### Lihat Konfigurasi

1. Klik **"Konfigurasi"** → **"Yuran Tahunan"**
2. Senarai konfigurasi dipaparkan

#### Tambah Konfigurasi Baru

Untuk tahun baru atau perubahan yuran:

1. Klik **"Tambah Yuran Baru"**
2. Isi maklumat:
   - **Nama**: "Yuran Tahunan 2026"
   - **Amaun**: RM10.00 (sebulan)
   - **Tahun**: 2026
   - **Status**: Aktif
3. Klik **"Simpan"**

#### Edit Konfigurasi

1. Klik pada konfigurasi
2. Klik **"Edit"**
3. Kemaskini maklumat
4. Klik **"Simpan"**

**⚠️ NOTA:** Perubahan konfigurasi TIDAK menjejaskan bil sedia ada.

### 8.2 Cara Amaun Yuran Berfungsi

```
Konfigurasi: RM10/bulan
│
└─► Jana Bil 2026
    ├─► Januari 2026: RM10
    ├─► Februari 2026: RM10
    ├─► ... (12 bulan)
    └─► Disember 2026: RM10
    
    Jumlah setahun: RM120
```

---

## 9. YURAN KEAHLIAN

### 9.1 Tentang Yuran Keahlian

- Bayaran **sekali sahaja** untuk menjadi ahli PPTT
- Dikenakan kepada **pemilik baru**
- **Reset** bila pemilik bertukar
- Perlu bayar sebelum bil tahunan dijana

### 9.2 Konfigurasi Yuran Keahlian

1. Klik **"Konfigurasi"** → **"Yuran Keahlian"** → **"Konfigurasi"**
2. Tetapkan:
   - **Amaun**: RM20.00
   - **Tahun Bermula**: 2025
   - **Status**: Aktif

### 9.3 Senarai Yuran Keahlian

1. Klik **"Konfigurasi"** → **"Yuran Keahlian"**
2. Senarai yuran keahlian penduduk dipaparkan

### 9.4 Tandakan Dibayar (Manual)

Untuk pembayaran manual (tunai/bank):

1. Cari yuran keahlian
2. Klik **"Tandakan Dibayar"**
3. Masukkan:
   - Tarikh bayar
   - Rujukan
   - Nota
4. Klik **"Simpan"**

---

## 10. TETAPAN SISTEM

### 10.1 Akses Tetapan

1. Klik **"Tetapan"** dari menu
2. Halaman tetapan dipaparkan

### 10.2 Tetapan ToyyibPay

ToyyibPay adalah payment gateway untuk pembayaran dalam talian.

#### Maklumat Diperlukan:

| Field | Keterangan |
|-------|------------|
| **User Secret Key** | Kunci rahsia dari ToyyibPay |
| **Category Code** | Kod kategori bil |
| **Collection Name** | Nama kutipan |

#### Cara Mendapatkan:

1. Login ke **toyyibpay.com**
2. Pergi ke **Settings** → **API**
3. Salin **User Secret Key**
4. Pergi ke **Bills** → **Category**
5. Salin **Category Code**

#### Kemaskini Tetapan:

1. Masukkan maklumat dalam borang
2. Klik **"Simpan Tetapan ToyyibPay"**

### 10.3 Tetapan Telegram

Sistem boleh hantar notifikasi ralat kepada Telegram.

#### Maklumat Diperlukan:

| Field | Keterangan |
|-------|------------|
| **Bot Token** | Token bot Telegram |
| **Chat ID** | ID chat/group Telegram |

#### Cara Setup:

1. Buat bot baru dengan **@BotFather** di Telegram
2. Dapatkan **Bot Token**
3. Tambah bot ke group
4. Dapatkan **Chat ID**

#### Kemaskini Tetapan:

1. Masukkan Bot Token dan Chat ID
2. Klik **"Simpan Tetapan Telegram"**
3. Klik **"Test"** untuk uji

---

## 11. LOG AUDIT

### 11.1 Akses Log Audit

1. Klik **"Log Audit"** dari menu
2. Senarai log dipaparkan

### 11.2 Maklumat Log

| Field | Keterangan |
|-------|------------|
| Tarikh/Masa | Bila aktiviti berlaku |
| Pengguna | Siapa yang buat |
| Tindakan | Create/Update/Delete |
| Model | Jenis data terkesan |
| Keterangan | Butiran perubahan |
| IP | Alamat IP |

### 11.3 Penapis Log

Boleh menapis mengikut:
- Pengguna
- Jenis tindakan
- Jenis model
- Julat tarikh

### 11.4 Kepentingan Log Audit

- ✅ Jejak siapa buat apa
- ✅ Bukti untuk audit
- ✅ Kesan aktiviti mencurigakan
- ✅ Pemulihan jika berlaku ralat

---

## 12. PENYELENGGARAAN SISTEM

### 12.1 Tugas Harian

- [ ] Semak pembayaran baru
- [ ] Sahkan pendaftaran pengguna (jika ada)
- [ ] Pantau notifikasi Telegram untuk ralat

### 12.2 Tugas Mingguan

- [ ] Semak laporan kutipan
- [ ] Pantau bil tertunggak
- [ ] Semak log audit untuk anomali

### 12.3 Tugas Bulanan

- [ ] Hantar peringatan bil (jika perlu)
- [ ] Semak rekonsiliasi pembayaran
- [ ] Backup data (jika diperlukan)

### 12.4 Tugas Tahunan

- [ ] Kemaskini konfigurasi yuran (jika ada perubahan)
- [ ] Jana bil untuk tahun baru
- [ ] Sediakan laporan untuk AGM
- [ ] Semak dan kemaskini kata laluan

### 12.5 Prosedur Jana Bil Tahunan

```
CHECKLIST JANA BIL TAHUNAN
══════════════════════════

SEBELUM AGM:
□ Sediakan cadangan yuran untuk tahun depan
□ Bentangkan kepada AGM untuk kelulusan

SELEPAS AGM:
□ Kemaskini konfigurasi yuran (jika berubah)
□ Pastikan senarai rumah ahli adalah terkini
□ Pastikan semua pemilik baru sudah direkod

JANA BIL:
□ Login sebagai Super Admin
□ Pergi ke Bil → Jana Bil
□ Pilih tahun yang betul
□ Semak bilangan rumah dan amaun
□ Klik "Jana Bil"
□ Sahkan bil dijana dengan betul

SELEPAS JANA:
□ Maklumkan kepada penduduk (e-mel/WhatsApp)
□ Pantau pembayaran awal
```

---

## 13. SOALAN LAZIM (FAQ)

### Q1: Bagaimana jika tersilap jana bil?

**Jawapan**:
1. Jika bil belum dibayar, anda boleh **padam** bil tersebut
2. Jika bil sudah dibayar, hubungi pembangun sistem untuk bantuan
3. **Tip**: Sentiasa semak sebelum jana bil

### Q2: Bolehkah saya refund pembayaran?

**Jawapan**: Refund perlu dilakukan melalui:
1. Dashboard **ToyyibPay** secara terus
2. Atau hubungi ToyyibPay support

### Q3: Bagaimana hendak reset kata laluan pengguna?

**Jawapan**: Pengguna perlu:
1. Klik "Lupa Kata Laluan" di halaman login
2. Masukkan e-mel
3. Ikut pautan dalam e-mel

Super Admin tidak boleh reset kata laluan pengguna secara terus.

### Q4: Apa yang berlaku jika pemilik tidak bayar yuran keahlian?

**Jawapan**:
1. Mereka tidak akan menjadi ahli rasmi
2. Bil tahunan TIDAK akan dijana untuk rumah tersebut
3. Mereka masih boleh bayar yuran keahlian bila-bila masa

### Q5: Bagaimana hendak tambah rumah baru (fasa baru)?

**Jawapan**:
1. Pergi ke **Rumah** → **Tambah Rumah**
2. Masukkan maklumat rumah baru
3. Assign pemilik jika sudah diketahui

### Q6: Adakah data backup secara automatik?

**Jawapan**: Sistem di-host di DigitalOcean dengan:
- Database backup harian
- Untuk backup tambahan, hubungi pembangun sistem

### Q7: Bagaimana jika ToyyibPay tidak berfungsi?

**Jawapan**:
1. Semak status ToyyibPay (toyyibpay.com/status)
2. Semak tetapan sistem (API Key masih sah?)
3. Maklumkan kepada penduduk tentang masalah sementara
4. Hubungi ToyyibPay support jika berterusan

### Q8: Bolehkah saya jadualkan penghantaran peringatan automatik?

**Jawapan**: Pada versi semasa, peringatan perlu dihantar secara manual. Fungsi automatik boleh ditambah pada versi akan datang.

### Q9: Bagaimana hendak export data untuk AGM?

**Jawapan**:
1. Buka laporan yang dikehendaki
2. Gunakan **Print** (Ctrl+P)
3. Pilih **Save as PDF**
4. Atau minta pembangun untuk export penuh

### Q10: Siapa yang boleh saya hubungi jika ada masalah teknikal?

**Jawapan**: Untuk masalah teknikal yang tidak dapat diselesaikan:
1. Dokumentasikan masalah (screenshot, langkah-langkah)
2. Hubungi pembangun sistem
3. Sertakan log dari Telegram (jika ada notifikasi ralat)

---

## 📋 RINGKASAN TUGAS SUPER ADMIN

### Tugas Utama

| Tugas | Kekerapan | Penting |
|-------|-----------|---------|
| Sahkan pengguna baru | Bila ada pendaftaran | ⭐⭐⭐ |
| Pantau pembayaran | Harian | ⭐⭐ |
| Jana bil tahunan | Setahun sekali | ⭐⭐⭐ |
| Hantar peringatan | Bila perlu | ⭐⭐ |
| Semak log audit | Mingguan | ⭐ |
| Kemaskini konfigurasi | Bila ada perubahan | ⭐⭐⭐ |

### Akses Eksklusif Super Admin

- ➕ Jana bil tahunan
- ❌ Padam bil
- ⚙️ Tetapan sistem (ToyyibPay/Telegram)
- 🔐 Pengurusan penuh rumah dan penduduk

---

## 📞 SOKONGAN TEKNIKAL

### Untuk Masalah Sistem

| Jenis Masalah | Tindakan |
|---------------|----------|
| Login tidak berfungsi | Semak e-mel/kata laluan |
| Pembayaran tidak update | Semak dengan ToyyibPay |
| Ralat sistem | Semak notifikasi Telegram |
| Masalah serius | Hubungi pembangun |

### Dokumentasi Teknikal

Untuk rujukan teknikal lanjut:
- `SYSTEM_SPEC_TAMAN_TROPIKA_KAJANG.md`
- `DEPLOYMENT_GUIDE.md`
- `DOCKER_DEPLOYMENT_GUIDE.md`

---

*Dokumen ini disediakan untuk Super Admin PPTT. Akses penuh memerlukan tanggungjawab tinggi.*

**© 2025 Persatuan Penduduk Taman Tropika Kajang**


