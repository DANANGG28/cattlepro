# 📥 Panduan Import Data Sapi - CattlePro

## Format File yang Didukung

CattlePro sekarang mendukung **3 format file** untuk import data sapi:

- ✅ **CSV** (.csv)
- ✅ **Excel 2007+** (.xlsx)
- ✅ **Excel 97-2003** (.xls)

## Validasi File

### Frontend Validation (JavaScript)
- ✅ Validasi extension file (hanya csv, xlsx, xls)
- ✅ Validasi ukuran file (maksimal 5MB)
- ✅ Preview nama file dan ukuran
- ✅ Visual feedback (icon berubah hijau jika valid, merah jika invalid)
- ✅ Disable submit button jika file invalid

### Backend Validation (PHP)
- ✅ Validasi extension file
- ✅ Validasi file dapat dibaca
- ✅ Validasi file tidak kosong
- ✅ Validasi struktur data (minimal 4 kolom)
- ✅ Skip baris kosong otomatis
- ✅ Skip baris panduan otomatis
- ✅ Validasi status reproduksi (whitelist)
- ✅ Limit panjang data (kode sapi & jenis max 50 karakter)

## Struktur Template

| Kode Sapi | Jenis | Tanggal Lahir | Berat | Status | Tanggal Status |
|-----------|-------|---------------|-------|--------|----------------|
| S001 | Limosin | 2023-01-15 | 350 | Kosong | |
| S002 | Simental | 2022-06-20 | 420 | Sudah Birahi | 2024-01-10 |

### Kolom Wajib:
1. **Kode Sapi** - Unique identifier (max 50 karakter)
2. **Jenis** - Jenis/ras sapi (max 50 karakter)
3. **Tanggal Lahir** - Format: YYYY-MM-DD
4. **Berat** - Berat dalam kg (angka)

### Kolom Opsional:
5. **Status** - Pilihan: `Kosong`, `Sudah Birahi`, `Sudah IB`, `Bunting`
6. **Tanggal Status** - Format: YYYY-MM-DD (wajib jika ada status)

## Cara Penggunaan

### 1. Download Template
- Klik tombol "Download Template Excel" di halaman import
- Template sudah berisi format dan contoh data

### 2. Isi Data
- Buka template di Microsoft Excel, Google Sheets, atau LibreOffice
- Isi data sapi sesuai kolom yang tersedia
- Pastikan format tanggal: YYYY-MM-DD (contoh: 2024-01-15)
- Untuk status, pilih salah satu: Kosong, Sudah Birahi, Sudah IB, Bunting

### 3. Simpan File
- **Untuk CSV**: File → Save As → pilih "CSV (Comma delimited)"
- **Untuk XLSX**: Simpan langsung (default Excel format)
- **Untuk XLS**: File → Save As → pilih "Excel 97-2003 Workbook"

### 4. Upload File
- Klik area upload atau drag & drop file
- Sistem akan validasi format dan ukuran file
- Jika valid, icon berubah hijau dan menampilkan nama file
- Klik "Mulai Proses Import"

### 5. Hasil Import
- Sistem akan menampilkan jumlah data berhasil dan gagal
- Data yang berhasil langsung masuk ke database
- Baris kosong dan baris panduan otomatis dilewati

## Error Handling

### Error yang Mungkin Muncul:

1. **"Format file tidak didukung"**
   - Pastikan file berformat CSV, XLSX, atau XLS
   - Cek extension file sudah benar

2. **"File terlalu besar! Maksimal 5MB"**
   - Kurangi jumlah baris data
   - Atau split menjadi beberapa file

3. **"File kosong atau tidak dapat dibaca"**
   - Pastikan file tidak corrupt
   - Pastikan ada data di dalam file

4. **"Tidak ada data valid yang bisa di-import"**
   - Cek format data sesuai template
   - Pastikan minimal ada 4 kolom terisi

5. **"Gagal membaca file XLSX"**
   - File mungkin corrupt atau password-protected
   - Coba save ulang atau convert ke CSV

## Tips & Best Practices

### ✅ DO:
- Gunakan template yang disediakan
- Pastikan format tanggal konsisten (YYYY-MM-DD)
- Isi minimal 4 kolom wajib
- Gunakan status yang valid
- Test dengan data kecil dulu (5-10 baris)

### ❌ DON'T:
- Jangan ubah header kolom
- Jangan gunakan format tanggal lain (DD/MM/YYYY, MM-DD-YYYY)
- Jangan kosongkan kolom wajib
- Jangan upload file >5MB
- Jangan gunakan karakter special di kode sapi

## Technical Details

### Library yang Digunakan:
- **ExcelReader.php** - Custom reader class
- **SimpleXLSX.php** - Lightweight XLSX parser (PHP 5.6+ compatible)
- **ZipArchive** - Built-in PHP extension untuk extract XLSX

### Supported PHP Version:
- PHP 5.6+ (dengan SimpleXLSX)
- PHP 8.2+ (recommended)

### Browser Compatibility:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Troubleshooting

### File XLSX tidak terbaca?
1. Pastikan file tidak password-protected
2. Coba buka dan save ulang di Excel
3. Atau convert ke CSV terlebih dahulu

### Data tidak masuk semua?
1. Cek baris yang kosong atau tidak lengkap
2. Cek format tanggal sudah benar
3. Cek status reproduksi sesuai pilihan yang valid
4. Lihat pesan error untuk detail baris yang gagal

### Upload stuck/loading lama?
1. Cek ukuran file (maksimal 5MB)
2. Cek koneksi internet
3. Coba refresh halaman dan upload ulang

## Support

Jika mengalami masalah, hubungi:
- Email: support@cattlepro.com
- WhatsApp: +62 xxx xxxx xxxx

---

**Last Updated:** 2024-01-17
**Version:** 2.0.0
