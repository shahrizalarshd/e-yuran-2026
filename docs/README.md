# 📚 DOKUMENTASI SISTEM e-YURAN PPTT
## Persatuan Penduduk Taman Tropika Kajang

---

## 📋 Senarai Dokumen

Folder ini mengandungi manual pengguna untuk setiap peranan dalam sistem e-Yuran:

| Dokumen | Untuk | Fail |
|---------|-------|------|
| 📘 **Manual Pengguna** | Penduduk / Ahli PPTT | `MANUAL_PENGGUNA.md` |
| 📗 **Manual Bendahari** | Bendahari PPTT | `MANUAL_BENDAHARI.md` |
| 📙 **Manual Auditor** | Juruaudit PPTT | `MANUAL_AUDITOR.md` |
| 📕 **Manual Super Admin** | Pentadbir Sistem | `MANUAL_SUPER_ADMIN.md` |

---

## 🔄 Cara Tukar ke PDF

### Pilihan 1: Menggunakan Pelayar Web

1. Buka fail `.md` dalam pelayar (GitHub, VS Code Preview, dll.)
2. Tekan `Ctrl + P` (atau `Cmd + P` pada Mac)
3. Pilih **"Save as PDF"** sebagai destinasi printer
4. Klik **"Save"**

### Pilihan 2: Menggunakan Pandoc (Terminal)

```bash
# Install pandoc (jika belum ada)
# macOS: brew install pandoc
# Ubuntu: sudo apt install pandoc

# Tukar ke PDF
pandoc MANUAL_PENGGUNA.md -o MANUAL_PENGGUNA.pdf
pandoc MANUAL_BENDAHARI.md -o MANUAL_BENDAHARI.pdf
pandoc MANUAL_AUDITOR.md -o MANUAL_AUDITOR.pdf
pandoc MANUAL_SUPER_ADMIN.md -o MANUAL_SUPER_ADMIN.pdf
```

### Pilihan 3: Menggunakan VS Code

1. Install extension "Markdown PDF"
2. Buka fail `.md`
3. Tekan `Ctrl + Shift + P`
4. Pilih **"Markdown PDF: Export (pdf)"**

### Pilihan 4: Menggunakan Online Converter

1. Pergi ke https://www.markdowntopdf.com/
2. Upload atau paste kandungan fail `.md`
3. Klik "Convert"
4. Download PDF

---

## 📧 Maklumat Login

| Peranan | E-mel | Kata Laluan |
|---------|-------|-------------|
| **Super Admin** | admin@pptt.my | *(hubungi pentadbir)* |
| **Bendahari** | bendahari@pptt.my | *(hubungi pentadbir)* |
| **Auditor** | auditor@pptt.my | *(hubungi pentadbir)* |
| **Penduduk** | *(e-mel peribadi)* | *(kata laluan peribadi)* |

---

## 🌐 URL Sistem

- **Production**: https://eyuran.pptt.my
- **Local Development**: http://localhost:8000

---

## 📞 Sokongan

Untuk sebarang pertanyaan atau bantuan:

1. Semak manual yang berkaitan dengan peranan anda
2. Hubungi Bendahari atau Super Admin
3. Untuk masalah teknikal, hubungi pembangun sistem

---

**Versi Dokumentasi**: 1.0  
**Tarikh Kemaskini**: 30 Disember 2025

**© 2025 Persatuan Penduduk Taman Tropika Kajang**

