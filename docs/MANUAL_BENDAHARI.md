# 📘 MANUAL BENDAHARI
## Sistem e-Yuran PPTT
### Persatuan Penduduk Taman Tropika Kajang

---

**Versi**: 1.0  
**Tarikh Kemaskini**: 30 Disember 2025  
**Untuk**: Bendahari PPTT

---

## 📋 ISI KANDUNGAN

1. [Pengenalan](#1-pengenalan)
2. [Cara Login](#2-cara-login)
3. [Dashboard Bendahari](#3-dashboard-bendahari)
4. [Pengurusan Bil](#4-pengurusan-bil)
5. [Pengurusan Pembayaran](#5-pengurusan-pembayaran)
6. [Laporan Kewangan](#6-laporan-kewangan)
7. [Pengesahan Pengguna](#7-pengesahan-pengguna)
8. [Konfigurasi Yuran](#8-konfigurasi-yuran)
9. [Yuran Keahlian](#9-yuran-keahlian)
10. [Hantar Peringatan](#10-hantar-peringatan)
11. [Soalan Lazim (FAQ)](#11-soalan-lazim-faq)

---

## 1. PENGENALAN

### 1.1 Peranan Bendahari

Sebagai Bendahari PPTT, anda bertanggungjawab untuk:

- ✅ Memantau kutipan yuran
- ✅ Menyemak dan merekod pembayaran
- ✅ Menjana laporan kewangan
- ✅ Mengesahkan pendaftaran pengguna baru
- ✅ Mengurus konfigurasi yuran
- ✅ Menghantar peringatan bil kepada penduduk

### 1.2 Akses Bendahari

| Fungsi | Boleh Lihat | Boleh Edit |
|--------|-------------|------------|
| Dashboard Analitik | ✅ | - |
| Senarai Rumah | ✅ | ❌ |
| Senarai Bil | ✅ | ✅ (terhad) |
| Senarai Pembayaran | ✅ | ❌ |
| Laporan Kewangan | ✅ | - |
| Pengesahan Pengguna | ✅ | ✅ |
| Konfigurasi Yuran | ✅ | ✅ |
| Hantar Peringatan | ✅ | ✅ |
| Audit Log | ❌ | ❌ |
| Tetapan Sistem | ❌ | ❌ |

### 1.3 Maklumat Login

| Maklumat | Nilai |
|----------|-------|
| **URL** | https://eyuran.pptt.my |
| **E-mel** | bendahari@pptt.my |
| **Kata Laluan** | *(hubungi Super Admin)* |

---

## 2. CARA LOGIN

### 2.1 Langkah Login

1. Buka pelayar web: **https://eyuran.pptt.my**
2. Klik **"Log Masuk"**
3. Masukkan e-mel: `bendahari@pptt.my`
4. Masukkan kata laluan
5. Klik **"Log Masuk"**
6. Anda akan dialihkan ke **Dashboard Admin**

### 2.2 Keselamatan

- ⚠️ Jangan kongsi kata laluan
- ⚠️ Log keluar selepas selesai
- ⚠️ Tukar kata laluan secara berkala
- ⚠️ Gunakan komputer yang selamat

---

## 3. DASHBOARD BENDAHARI

### 3.1 Statistik Utama

Dashboard memaparkan 4 kad statistik:

```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ 💰 KUTIPAN   │ │ ⚠️ TERTUNGGAK│ │ 🏠 RUMAH     │ │ ⏰ LEWAT     │
│ RM 15,240    │ │ RM 3,600     │ │ 18/20        │ │ 12           │
│ Jumlah tahun │ │ Belum bayar  │ │ Ahli aktif   │ │ Bil lewat    │
└──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
```

| Statistik | Maksud |
|-----------|--------|
| **Kutipan** | Jumlah kutipan tahun semasa |
| **Tertunggak** | Jumlah bil yang belum dibayar |
| **Rumah Ahli** | Bilangan rumah dengan ahli aktif |
| **Bil Lewat** | Bilangan bil yang melepasi tarikh akhir |

### 3.2 Butang Tindakan Cepat

| Butang | Fungsi |
|--------|--------|
| **Laporan Tertunggak** | Lihat senarai rumah dengan tunggakan |
| **Laporan Kutipan** | Lihat laporan kutipan bulanan |
| **Pengesahan Pengguna** | Sahkan pendaftaran pengguna baru |

### 3.3 Analitik Kewangan

#### Carta Kutipan Bulanan
- Membandingkan kutipan tahun semasa vs tahun lepas
- Boleh tukar tahun perbandingan menggunakan dropdown

#### Kadar Kutipan
- Peratusan bil yang telah dibayar
- Contoh: 75% bermakna 75% daripada bil telah dibayar

#### Status Bil
- **Dibayar** (Hijau): Bil yang telah dijelaskan
- **Belum Bayar** (Merah): Bil yang belum dibayar
- **Separa** (Kuning): Bil yang dibayar sebahagian

#### Kutipan 7 Hari Terakhir
- Carta bar menunjukkan kutipan harian

### 3.4 Senarai Terkini

#### Pengesahan Menunggu
- Senarai pengguna yang menunggu pengesahan
- Butang ✅ untuk Luluskan
- Butang ❌ untuk Tolak

#### Pembayaran Terkini
- 5 pembayaran terkini
- Klik untuk lihat butiran

#### Rumah Tertunggak
- Senarai rumah dengan bil tertunggak
- Jumlah tunggakan setiap rumah

---

## 4. PENGURUSAN BIL

### 4.1 Lihat Senarai Bil

1. Dari menu, klik **"Bil"** → **"Senarai Bil"**
2. Senarai semua bil akan dipaparkan

### 4.2 Penapis Bil

Anda boleh menapis bil mengikut:

| Penapis | Pilihan |
|---------|---------|
| **Tahun** | 2023, 2024, 2025, ... |
| **Status** | Semua / Dibayar / Belum Bayar / Tertunggak |
| **Rumah** | Pilih rumah tertentu |

### 4.3 Butiran Bil

Klik pada bil untuk melihat:

| Maklumat | Keterangan |
|----------|------------|
| No. Bil | Nombor rujukan bil |
| Rumah | Alamat rumah |
| Tempoh | Bulan dan tahun bil |
| Jumlah | Amaun bil |
| Jumlah Dibayar | Amaun yang telah dibayar |
| Baki | Amaun yang masih perlu dibayar |
| Status | Dibayar / Belum Bayar |
| Tarikh Akhir | Tarikh akhir pembayaran |

### 4.4 Edit Bil

**Anda boleh edit bil yang BELUM DIBAYAR:**

1. Klik bil yang ingin diedit
2. Klik butang **"Edit"**
3. Kemaskini maklumat:
   - Jumlah bil
   - Tarikh akhir
   - Nota
4. Klik **"Simpan"**

**⚠️ PENTING:**
- Bil yang sudah dibayar TIDAK boleh diedit
- Semua perubahan direkodkan dalam Audit Log

### 4.5 Laporan Bil Tertunggak

1. Klik **"Bil"** → **"Laporan Tertunggak"**
2. Memaparkan:
   - Senarai rumah dengan tunggakan
   - Jumlah tunggakan setiap rumah
   - Jumlah bulan tertunggak

---

## 5. PENGURUSAN PEMBAYARAN

### 5.1 Lihat Senarai Pembayaran

1. Dari menu, klik **"Pembayaran"** → **"Senarai Pembayaran"**
2. Senarai semua pembayaran akan dipaparkan

### 5.2 Penapis Pembayaran

| Penapis | Pilihan |
|---------|---------|
| **Tahun** | 2023, 2024, 2025, ... |
| **Bulan** | Januari - Disember |
| **Status** | Berjaya / Gagal / Dalam Proses |

### 5.3 Butiran Pembayaran

Klik pada pembayaran untuk melihat:

| Maklumat | Keterangan |
|----------|------------|
| No. Pembayaran | Nombor rujukan unik |
| Rumah | Alamat rumah |
| Pembayar | Nama dan e-mel pembayar |
| Tarikh | Tarikh dan masa pembayaran |
| Jumlah | Amaun pembayaran |
| Kaedah | FPX / Kad Kredit |
| Status | Berjaya / Gagal |
| Rujukan ToyyibPay | Nombor rujukan dari ToyyibPay |
| Bil yang Dibayar | Senarai bil yang dilunaskan |

### 5.4 Rekonsiliasi Pembayaran

Untuk menyemak pembayaran dengan rekod bank:

1. Klik **"Pembayaran"** → **"Rekonsiliasi"**
2. Pilih julat tarikh
3. Semak senarai pembayaran dengan penyata bank

---

## 6. LAPORAN KEWANGAN

### 6.1 Laporan Kutipan

1. Klik **"Pembayaran"** → **"Laporan Kutipan"**
2. Pilih tahun dan bulan
3. Memaparkan:
   - Jumlah kutipan
   - Bilangan pembayaran
   - Carta kutipan bulanan
   - Pecahan mengikut bulan

### 6.2 Maklumat dalam Laporan

```
┌─────────────────────────────────────────┐
│  📊 LAPORAN KUTIPAN 2025                │
│                                         │
│  Jumlah Kutipan: RM 15,240.00          │
│  Bilangan Pembayaran: 127               │
│                                         │
│  PECAHAN BULANAN:                       │
│  ├─ Januari: RM 2,400 (20 pembayaran)   │
│  ├─ Februari: RM 1,800 (15 pembayaran)  │
│  └─ ...                                 │
└─────────────────────────────────────────┘
```

### 6.3 Export Laporan

Untuk tujuan mesyuarat atau audit:
1. Buka laporan yang dikehendaki
2. Gunakan fungsi **Print** pelayar (Ctrl+P)
3. Pilih **Save as PDF**

---

## 7. PENGESAHAN PENGGUNA

### 7.1 Proses Pengesahan

Apabila penduduk baru mendaftar:

1. Mereka memilih rumah dan hubungan
2. Status akaun: **"Menunggu Pengesahan"**
3. Bendahari/Super Admin perlu mengesahkan

### 7.2 Cara Mengesahkan

1. Klik **"Penduduk"** → **"Menunggu Pengesahan"**
2. Semak maklumat pendaftar:
   - Nama
   - E-mel
   - Rumah yang dipilih
   - Hubungan (Owner/Spouse/Child/Tenant)
3. Sahkan identiti (jika perlu, hubungi pendaftar)
4. Klik **✅ Luluskan** atau **❌ Tolak**

### 7.3 Kesan Pengesahan

| Tindakan | Kesan |
|----------|-------|
| **Luluskan** | Pengguna boleh akses sistem, e-mel notifikasi dihantar |
| **Tolak** | Pendaftaran ditolak, e-mel pemberitahuan dihantar |

### 7.4 Tips Pengesahan

- ✅ Pastikan rumah yang dipilih adalah betul
- ✅ Sahkan hubungan dengan owner sebenar
- ⚠️ Berhati-hati dengan pendaftaran mencurigakan
- 📞 Hubungi jika perlu pengesahan tambahan

---

## 8. KONFIGURASI YURAN

### 8.1 Lihat Konfigurasi Semasa

1. Klik **"Konfigurasi"** → **"Yuran Tahunan"**
2. Memaparkan senarai konfigurasi yuran

### 8.2 Tambah Konfigurasi Baru

Untuk menetapkan yuran baru (contoh: tahun 2026):

1. Klik **"Tambah Yuran Baru"**
2. Isi maklumat:
   - **Nama**: Yuran Tahunan 2026
   - **Amaun**: RM10.00
   - **Tahun**: 2026
   - **Status**: Aktif
3. Klik **"Simpan"**

### 8.3 Edit Konfigurasi

1. Klik pada konfigurasi yang ingin diedit
2. Klik **"Edit"**
3. Kemaskini maklumat
4. Klik **"Simpan"**

**⚠️ PENTING:**
- Perubahan konfigurasi TIDAK menjejaskan bil yang sedia ada
- Bil baru akan menggunakan konfigurasi terkini

---

## 9. YURAN KEAHLIAN

### 9.1 Tentang Yuran Keahlian

- Yuran sekali sahaja untuk menjadi ahli PPTT
- Dikenakan kepada owner baru
- Perlu dibayar sebelum bil tahunan dijana

### 9.2 Lihat Yuran Keahlian

1. Klik **"Konfigurasi"** → **"Yuran Keahlian"**
2. Memaparkan senarai yuran keahlian

### 9.3 Konfigurasi Yuran Keahlian

1. Klik **"Konfigurasi Yuran Keahlian"**
2. Tetapkan:
   - **Amaun**: RM20.00
   - **Tahun Bermula**: 2025
   - **Status**: Aktif

### 9.4 Tandakan Yuran Keahlian Dibayar

Untuk pembayaran manual (tunai/bank):

1. Cari yuran keahlian penduduk
2. Klik **"Edit"**
3. Pilih **"Tandakan Dibayar"**
4. Masukkan:
   - Tarikh pembayaran
   - Rujukan pembayaran
   - Nota (pilihan)
5. Klik **"Simpan"**

---

## 10. HANTAR PERINGATAN

### 10.1 Peringatan Bil

Untuk menghantar peringatan kepada penduduk yang ada tunggakan:

1. Klik **"Bil"** → **"Hantar Peringatan"**
2. Sistem memaparkan senarai rumah dengan tunggakan
3. Pilih rumah yang ingin dihantar peringatan
4. Klik **"Hantar Peringatan"**

### 10.2 Jenis Peringatan

| Jenis | Bila Dihantar | Kandungan |
|-------|---------------|-----------|
| **Peringatan Biasa** | Sebelum tarikh akhir | Peringatan untuk bayar |
| **Peringatan Tertunggak** | Selepas tarikh akhir | Notis bil tertunggak |

### 10.3 Hantar Peringatan Individu

Untuk hantar peringatan kepada satu rumah sahaja:

1. Pergi ke **"Bil"** → **"Laporan Tertunggak"**
2. Cari rumah yang dikehendaki
3. Klik butang **"Hantar Peringatan"** di sebelah rumah

### 10.4 Hantar Peringatan Pukal

Untuk hantar peringatan kepada semua rumah tertunggak:

1. Pergi ke **"Bil"** → **"Hantar Peringatan"**
2. Tapis mengikut kriteria (jika perlu)
3. Klik **"Hantar Semua Peringatan"**
4. Sahkan tindakan

**⚠️ AMARAN:** Pastikan anda benar-benar mahu hantar peringatan kepada semua.

---

## 11. SOALAN LAZIM (FAQ)

### Q1: Bagaimana jika penduduk bayar secara tunai?

**Jawapan**: Untuk pembayaran manual:
1. Rekodkan pembayaran dalam buku kewangan fizikal
2. Hubungi Super Admin untuk merekodkan dalam sistem
3. Super Admin akan menandakan bil sebagai dibayar

### Q2: Bolehkah saya jana bil secara manual?

**Jawapan**: Tidak. Hanya Super Admin boleh menjana bil. Hubungi Super Admin jika perlu.

### Q3: Bagaimana jika ada pembayaran duplikasi?

**Jawapan**:
1. Semak rekod pembayaran dalam sistem
2. Semak dengan ToyyibPay dashboard
3. Hubungi Super Admin untuk refund jika perlu

### Q4: Bolehkah saya padam bil?

**Jawapan**: Tidak. Bil tidak boleh dipadam untuk tujuan audit. Hubungi Super Admin untuk kes khas.

### Q5: Bagaimana jika pengguna tersalah pilih rumah?

**Jawapan**:
1. **Tolak** pendaftaran tersebut
2. Minta pengguna daftar semula dengan rumah yang betul

### Q6: Adakah saya boleh lihat log audit?

**Jawapan**: Tidak. Log audit hanya boleh dilihat oleh Super Admin dan Auditor.

### Q7: Bagaimana hendak export data untuk mesyuarat AJK?

**Jawapan**:
1. Buka laporan yang dikehendaki
2. Gunakan Print → Save as PDF
3. Atau minta Super Admin untuk export data

### Q8: Bila yuran tahunan perlu dibayar?

**Jawapan**: Mengikut konfigurasi semasa:
- Bil dijana pada awal tahun
- Tarikh akhir: 31 Januari setiap tahun
- *Tertakluk kepada keputusan AGM*

---

## 📞 SOKONGAN

### Hubungi Super Admin

Untuk isu yang memerlukan akses Super Admin:

| Isu | Tindakan |
|-----|----------|
| Jana bil | Hubungi Super Admin |
| Padam bil | Hubungi Super Admin |
| Refund pembayaran | Hubungi Super Admin |
| Tukar kata laluan | Hubungi Super Admin |
| Masalah sistem | Hubungi Super Admin |

### Waktu Operasi Sistem

- **Sistem**: 24 jam, 7 hari seminggu
- **Sokongan**: Isnin - Jumaat, 9 pagi - 5 petang

---

## 📝 CHECKLIST BULANAN BENDAHARI

### Setiap Minggu

- [ ] Semak pembayaran baru
- [ ] Sahkan pendaftaran pengguna
- [ ] Pantau bil tertunggak

### Setiap Bulan

- [ ] Semak laporan kutipan bulanan
- [ ] Hantar peringatan bil (jika perlu)
- [ ] Semak rekonsiliasi pembayaran
- [ ] Laporkan status kepada AJK

### Setiap Tahun

- [ ] Semak konfigurasi yuran tahun baru
- [ ] Sediakan laporan tahunan untuk AGM
- [ ] Backup rekod kewangan

---

*Dokumen ini disediakan untuk Bendahari PPTT. Untuk sebarang pertanyaan, sila hubungi Super Admin.*

**© 2025 Persatuan Penduduk Taman Tropika Kajang**

