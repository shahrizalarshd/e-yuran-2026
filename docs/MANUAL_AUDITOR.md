# 📘 MANUAL AUDITOR
## Sistem e-Yuran PPTT
### Persatuan Penduduk Taman Tropika Kajang

---

**Versi**: 1.0  
**Tarikh Kemaskini**: 30 Disember 2025  
**Untuk**: Juruaudit PPTT

---

## 📋 ISI KANDUNGAN

1. [Pengenalan](#1-pengenalan)
2. [Cara Login](#2-cara-login)
3. [Dashboard Auditor](#3-dashboard-auditor)
4. [Log Audit](#4-log-audit)
5. [Semakan Bil](#5-semakan-bil)
6. [Semakan Pembayaran](#6-semakan-pembayaran)
7. [Laporan Kewangan](#7-laporan-kewangan)
8. [Maklumat Rumah](#8-maklumat-rumah)
9. [Prosedur Audit](#9-prosedur-audit)
10. [Soalan Lazim (FAQ)](#10-soalan-lazim-faq)

---

## 1. PENGENALAN

### 1.1 Peranan Auditor

Sebagai Juruaudit PPTT, peranan anda adalah **menyemak dan mengesahkan** integriti data kewangan sistem. Anda mempunyai akses **baca-sahaja (read-only)** kepada:

- ✅ Log audit sistem
- ✅ Senarai bil dan pembayaran
- ✅ Laporan kewangan
- ✅ Maklumat rumah dan penduduk

### 1.2 Akses Auditor

| Fungsi | Boleh Lihat | Boleh Edit |
|--------|-------------|------------|
| Dashboard | ✅ | - |
| Senarai Rumah | ✅ | ❌ |
| Butiran Rumah | ✅ | ❌ |
| Senarai Bil | ✅ | ❌ |
| Senarai Pembayaran | ✅ | ❌ |
| Laporan Kewangan | ✅ | ❌ |
| **Log Audit** | ✅ | ❌ |
| Pengesahan Pengguna | ❌ | ❌ |
| Konfigurasi Yuran | ❌ | ❌ |
| Tetapan Sistem | ❌ | ❌ |

**⚠️ PENTING:** Auditor TIDAK boleh membuat sebarang perubahan data. Ini memastikan **integriti audit**.

### 1.3 Maklumat Login

| Maklumat | Nilai |
|----------|-------|
| **URL** | https://eyuran.pptt.my |
| **E-mel** | auditor@pptt.my |
| **Kata Laluan** | *(hubungi Super Admin)* |

---

## 2. CARA LOGIN

### 2.1 Langkah Login

1. Buka pelayar web: **https://eyuran.pptt.my**
2. Klik **"Log Masuk"**
3. Masukkan e-mel: `auditor@pptt.my`
4. Masukkan kata laluan
5. Klik **"Log Masuk"**

### 2.2 Selepas Login

Anda akan dialihkan ke **Dashboard Admin** dengan akses terhad mengikut peranan Auditor.

---

## 3. DASHBOARD AUDITOR

### 3.1 Statistik Ringkasan

Dashboard memaparkan statistik utama (baca sahaja):

```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ 💰 KUTIPAN   │ │ ⚠️ TERTUNGGAK│ │ 🏠 RUMAH     │ │ ⏰ LEWAT     │
│ RM 15,240    │ │ RM 3,600     │ │ 18/20        │ │ 12           │
│ Jumlah tahun │ │ Belum bayar  │ │ Ahli aktif   │ │ Bil lewat    │
└──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
```

### 3.2 Carta Analitik

- **Kutipan Bulanan**: Perbandingan kutipan tahun semasa vs tahun lepas
- **Kadar Kutipan**: Peratusan bil yang telah dibayar
- **Status Bil**: Pecahan bil mengikut status

### 3.3 Navigasi Auditor

Dari menu, anda boleh akses:
- Dashboard
- Rumah (lihat sahaja)
- Bil (lihat sahaja)
- Pembayaran (lihat sahaja)
- Laporan
- **Log Audit** ⭐ (akses eksklusif)

---

## 4. LOG AUDIT

### 4.1 Apa itu Log Audit?

Log Audit merekodkan **semua aktiviti penting** dalam sistem:

- Login/Logout pengguna
- Penciptaan data baru
- Kemaskini data
- Penghapusan data
- Perubahan sistem

### 4.2 Akses Log Audit

1. Dari menu, klik **"Log Audit"**
2. Senarai aktiviti akan dipaparkan

### 4.3 Maklumat dalam Log

Setiap rekod log mengandungi:

| Field | Keterangan |
|-------|------------|
| **Tarikh/Masa** | Bila aktiviti berlaku |
| **Pengguna** | Siapa yang melakukan |
| **Tindakan** | Jenis aktiviti (Create/Update/Delete) |
| **Model** | Jenis data yang terkesan |
| **Keterangan** | Butiran aktiviti |
| **Alamat IP** | Alamat IP pengguna |

### 4.4 Penapis Log

Anda boleh menapis log mengikut:

| Penapis | Pilihan |
|---------|---------|
| **Pengguna** | Pilih pengguna tertentu |
| **Tindakan** | Create / Update / Delete / Login |
| **Model** | Bill / Payment / House / User |
| **Dari Tarikh** | Tarikh mula |
| **Hingga Tarikh** | Tarikh akhir |

### 4.5 Jenis Tindakan

| Tindakan | Warna | Maksud |
|----------|-------|--------|
| **Create** | 🟢 Hijau | Data baru dicipta |
| **Update** | 🔵 Biru | Data dikemaskini |
| **Delete** | 🔴 Merah | Data dipadam |
| **Login** | 🟣 Ungu | Pengguna log masuk |
| **Logout** | ⚪ Kelabu | Pengguna log keluar |

### 4.6 Contoh Log Audit

```
┌────────────────────────────────────────────────────────────────┐
│ LOG AUDIT                                                       │
├────────────────────────────────────────────────────────────────┤
│ 30/12/2025 14:23:15 | admin@pptt.my | UPDATE | Bill #145       │
│ → Kemaskini bil Januari 2025: Jumlah RM10 → RM12              │
│ IP: 192.168.1.100                                              │
├────────────────────────────────────────────────────────────────┤
│ 30/12/2025 13:45:00 | bendahari@pptt.my | CREATE | Payment #78 │
│ → Pembayaran baru RM240 untuk Rumah No. 15                     │
│ IP: 192.168.1.101                                              │
├────────────────────────────────────────────────────────────────┤
│ 30/12/2025 10:00:00 | System | CREATE | Bill #200-220          │
│ → Jana bil Januari 2025 untuk 20 rumah                         │
│ IP: System                                                     │
└────────────────────────────────────────────────────────────────┘
```

### 4.7 Butiran Log

Klik pada mana-mana rekod untuk melihat butiran penuh:

- Data sebelum perubahan
- Data selepas perubahan
- Perbandingan perubahan

---

## 5. SEMAKAN BIL

### 5.1 Senarai Bil

1. Klik **"Bil"** dari menu
2. Semua bil akan dipaparkan

### 5.2 Penapis Bil

| Penapis | Pilihan |
|---------|---------|
| **Tahun** | 2023, 2024, 2025 |
| **Status** | Semua / Dibayar / Belum Bayar |
| **Rumah** | Pilih rumah tertentu |

### 5.3 Maklumat Bil

Untuk setiap bil, semak:

| Field | Perkara untuk Audit |
|-------|---------------------|
| **No. Bil** | Nombor unik dan berturutan |
| **Rumah** | Kaitkan dengan rumah yang betul |
| **Tempoh** | Bulan dan tahun yang betul |
| **Jumlah** | Sama dengan konfigurasi yuran |
| **Status** | Konsisten dengan rekod pembayaran |
| **Tarikh Bayar** | Sepadan dengan rekod pembayaran |

### 5.4 Laporan Tertunggak

1. Klik **"Bil"** → **"Laporan Tertunggak"**
2. Semak senarai rumah dengan tunggakan
3. Pastikan jumlah tunggakan adalah tepat

---

## 6. SEMAKAN PEMBAYARAN

### 6.1 Senarai Pembayaran

1. Klik **"Pembayaran"** dari menu
2. Semua pembayaran akan dipaparkan

### 6.2 Maklumat Pembayaran

Untuk setiap pembayaran, semak:

| Field | Perkara untuk Audit |
|-------|---------------------|
| **No. Pembayaran** | Nombor unik |
| **Tarikh** | Tarikh transaksi |
| **Rumah** | Kaitkan dengan rumah yang betul |
| **Jumlah** | Sepadan dengan bil |
| **Rujukan ToyyibPay** | Rujukan untuk pengesahan bank |
| **Bil yang Dibayar** | Bil yang dilunaskan |

### 6.3 Butiran Pembayaran

Klik pada pembayaran untuk melihat:

```
┌─────────────────────────────────────────┐
│  PEMBAYARAN #PAY-2025-0001              │
├─────────────────────────────────────────┤
│  Tarikh: 30/12/2025 14:23:15           │
│  Rumah: No. 15, Jalan Tropika 1        │
│  Pembayar: Ahmad bin Ali               │
│  Jumlah: RM 240.00                     │
│  Kaedah: FPX (Maybank)                 │
│  Status: ✅ Berjaya                     │
│  Rujukan: TP-123456789                 │
│                                         │
│  BIL YANG DIBAYAR:                      │
│  • Januari 2025 - RM20.00              │
│  • Februari 2025 - RM20.00             │
│  • ... (12 bulan)                      │
└─────────────────────────────────────────┘
```

---

## 7. LAPORAN KEWANGAN

### 7.1 Laporan Kutipan

1. Klik **"Pembayaran"** → **"Laporan Kutipan"**
2. Pilih tahun untuk semak
3. Semak:
   - Jumlah kutipan tahunan
   - Pecahan kutipan bulanan
   - Bilangan transaksi

### 7.2 Perkara untuk Semak

| Aspek | Semakan |
|-------|---------|
| **Ketepatan** | Jumlah kutipan sepadan dengan rekod pembayaran |
| **Kelengkapan** | Semua pembayaran direkodkan |
| **Konsistensi** | Status bil sepadan dengan pembayaran |

### 7.3 Perbandingan Tahun

- Bandingkan kutipan tahun semasa dengan tahun lepas
- Kenalpasti trend dan anomali

---

## 8. MAKLUMAT RUMAH

### 8.1 Senarai Rumah

1. Klik **"Rumah"** dari menu
2. Semua rumah dalam taman akan dipaparkan

### 8.2 Butiran Rumah

Untuk setiap rumah, anda boleh lihat:

| Maklumat | Keterangan |
|----------|------------|
| **Alamat** | Nombor dan jalan |
| **Status** | Diduduki / Kosong |
| **Keahlian** | Ahli PPTT / Bukan Ahli |
| **Pemilik** | Nama pemilik semasa |
| **Sejarah Bil** | Senarai bil rumah |
| **Sejarah Bayar** | Senarai pembayaran rumah |

### 8.3 Semakan Keahlian

Pastikan:
- Status keahlian adalah tepat
- Yuran keahlian telah dibayar (jika ahli)
- Bil tahunan hanya dijana untuk ahli

---

## 9. PROSEDUR AUDIT

### 9.1 Audit Berkala

#### Semakan Mingguan

- [ ] Semak log audit untuk aktiviti luar biasa
- [ ] Pastikan tiada perubahan data tanpa kebenaran

#### Semakan Bulanan

- [ ] Semak laporan kutipan bulanan
- [ ] Bandingkan dengan rekod bank (jika ada akses)
- [ ] Sahkan bilangan pembayaran

#### Semakan Tahunan

- [ ] Audit penuh untuk laporan AGM
- [ ] Semak semua bil dan pembayaran
- [ ] Sediakan laporan audit

### 9.2 Perkara Penting untuk Audit

#### A. Integriti Data

1. **Bil dijana dengan betul**
   - Jumlah sepadan dengan konfigurasi yuran
   - Bil hanya untuk ahli aktif
   - Tiada bil duplikasi

2. **Pembayaran direkod dengan tepat**
   - Jumlah sepadan dengan bil
   - Status bil dikemaskini selepas bayar
   - Rujukan ToyyibPay ada

3. **Perubahan data dilog**
   - Semua kemaskini bil ada dalam log
   - Pengguna yang buat perubahan direkod

#### B. Kawalan Akses

1. **Pengguna dan peranan**
   - Auditor hanya boleh baca
   - Bendahari tidak boleh padam bil
   - Hanya Super Admin boleh tukar tetapan

2. **Log login**
   - Semua login direkod
   - Tiada akses tanpa kebenaran

### 9.3 Red Flags (Amaran)

Berhati-hati jika mendapati:

| Red Flag | Kemungkinan Isu |
|----------|-----------------|
| Bil diedit selepas bayar | Manipulasi data |
| Pembayaran tanpa rujukan | Pembayaran manual tidak sah |
| Banyak login gagal | Cubaan akses tanpa kebenaran |
| Perubahan besar pada data lama | Kemungkinan fraud |
| Bil dipadam | Sorokkan maklumat |

### 9.4 Format Laporan Audit

```
┌─────────────────────────────────────────┐
│  LAPORAN AUDIT e-YURAN PPTT             │
│  Tahun Kewangan: 2025                   │
├─────────────────────────────────────────┤
│                                         │
│  1. RINGKASAN                           │
│     - Jumlah bil dijana: 240            │
│     - Jumlah pembayaran: 180            │
│     - Jumlah kutipan: RM 18,000        │
│                                         │
│  2. PENEMUAN                            │
│     - Tiada isu material dijumpai       │
│     - Semua pembayaran direkod betul    │
│                                         │
│  3. CADANGAN                            │
│     - Tiada cadangan khusus             │
│                                         │
│  4. KESIMPULAN                          │
│     - Rekod kewangan adalah tepat       │
│       dan boleh dipercayai              │
│                                         │
│  Disediakan oleh:                       │
│  [Nama Auditor]                         │
│  Tarikh: [Tarikh]                       │
└─────────────────────────────────────────┘
```

---

## 10. SOALAN LAZIM (FAQ)

### Q1: Adakah saya boleh edit data?

**Jawapan**: Tidak. Peranan Auditor adalah **baca-sahaja** untuk memastikan integriti audit. Ini bermakna tiada conflict of interest.

### Q2: Bagaimana jika saya jumpa ralat data?

**Jawapan**: 
1. Dokumentasikan penemuan
2. Maklumkan kepada Super Admin atau Bendahari
3. Minta mereka membuat pembetulan
4. Semak log audit untuk pastikan pembetulan direkod

### Q3: Bolehkah saya export data?

**Jawapan**: Anda boleh:
1. Gunakan fungsi Print pelayar (Ctrl+P)
2. Pilih "Save as PDF"
3. Untuk export penuh, minta Super Admin

### Q4: Siapa yang boleh akses Log Audit?

**Jawapan**: Hanya **Super Admin** dan **Auditor** boleh akses Log Audit. Bendahari dan penduduk tidak boleh melihat log ini.

### Q5: Berapa lama log audit disimpan?

**Jawapan**: Log audit disimpan **selama-lamanya** untuk tujuan rujukan dan compliance.

### Q6: Bagaimana hendak sahkan pembayaran dengan bank?

**Jawapan**: 
1. Gunakan **Rujukan ToyyibPay** dalam butiran pembayaran
2. Semak dengan penyata ToyyibPay (minta akses dari Super Admin)
3. Atau minta Bendahari untuk rekonsiliasi

### Q7: Apakah yang perlu dilaporkan dalam AGM?

**Jawapan**: Laporan audit untuk AGM patut mengandungi:
1. Jumlah kutipan tahun tersebut
2. Jumlah tertunggak
3. Sebarang isu atau penemuan
4. Pengesahan integriti data
5. Cadangan penambahbaikan (jika ada)

---

## 📋 CHECKLIST AUDIT TAHUNAN

### Sebelum AGM

- [ ] Login ke sistem dan semak akses
- [ ] Export laporan kutipan tahunan
- [ ] Semak log audit untuk anomali
- [ ] Bandingkan bil vs pembayaran
- [ ] Sahkan status semua rumah ahli
- [ ] Semak yuran keahlian dibayar
- [ ] Sediakan laporan audit
- [ ] Bentangkan penemuan kepada AJK

### Dokumen Sokongan

- [ ] Laporan Kutipan Tahunan
- [ ] Senarai Bil Tertunggak
- [ ] Log Audit (sampel)
- [ ] Senarai Ahli Aktif

---

## 📞 SOKONGAN

### Hubungi Super Admin

Untuk sebarang isu atau pertanyaan:

| Isu | Hubungi |
|-----|---------|
| Akses sistem | Super Admin |
| Reset kata laluan | Super Admin |
| Export data | Super Admin |
| Penemuan audit | Super Admin & Bendahari |

---

*Dokumen ini disediakan untuk Juruaudit PPTT. Akses baca-sahaja memastikan integriti audit.*

**© 2025 Persatuan Penduduk Taman Tropika Kajang**

