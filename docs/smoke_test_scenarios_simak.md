# Smoke Test Scenarios: SIMAK Kontrak Konstruksi & Konsultasi

**Target URLs:**
- Konstruksi: `https://satkerpps-riau.online/admin/kontrak/simak/konstruksi`
- Konsultasi: `https://satkerpps-riau.online/admin/kontrak/simak/konsultasi`

**Akun Testing:**
- Email: `agung.justik@gmail.com`
- Password: (sesuaikan dengan akun Anda)

---

## SCENARIO 1: Input Data Baru (Create)

### Tujuan
Memastikan fitur input data SIMAK baru berfungsi dengan benar.

### Prasyarat
- User memiliki role: Admin / Editor / Super Admin
- Login dengan akun: `agung.justik@gmail.com`
- Akses ke halaman daftar SIMAK

### Langkah Testing

#### Konstruksi (KON-01)
1. Login ke `https://satkerpps-riau.online` dengan email `agung.justik@gmail.com`
2. Buka halaman `https://satkerpps-riau.online/admin/kontrak/simak/konstruksi`
3. Klik tombol **"Input Data SIMAK"** (warna biru)
4. Modal form akan muncul
5. Isi field-field berikut:
   - **PPK**: Pilih dari dropdown (contoh: NIP 199012212018021001)
   - **Nama Paket**: `Test Paket Konstruksi Smoke [YYYYMMDDHHMMSS]`
   - **Tahun Anggaran**: Pilih (default: tahun berjalan)
   - **Penyedia**: `CV Test Konstruksi Indonesia`
   - **Nomor Kontrak**: `PLN/SIMAK-KONSTRUKSI/[NOMOR]/2024`
   - **Nilai Kontrak (Rp)**: `500000000` (500 juta)
   - **Email Responden 1**: `test.konstruksi@example.com`
   - **Email Responden 2**: `responden.kedua@example.com`
6. Klik tombol **"Simpan"**
7. **Verifikasi**:
   - [ ] Modal menutup
   - [ ] Muncul pesan success (alert hijau)
   - [ ] Data baru muncul di tabel daftar SIMAK
   - [ ] Nomor kontrak sesuai dengan yang diinput
   - [ ] Nilai kontrak tertampilkan dengan formatRp (ribuan)

#### Konsultasi (KON-02)
1. Buka halaman `https://satkerpps-riau.online/admin/kontrak/simak/konsultasi`
2. Klik tombol **"Input Data SIMAK"**
3. Isi field-field berikut:
   - **PPK**: Pilih dari dropdown
   - **Nama Paket**: `Test Paket Konsultasi Smoke [YYYYMMDDHHMMSS]`
   - **Tahun Anggaran**: Pilih
   - **Penyedia**: `PT Test Konsultasi Indonesia`
   - **Nomor Kontrak**: `PLN/SIMAK-KONSULTASI/[NOMOR]/2024`
   - **Nilai Kontrak (Rp)**: `250000000` (250 juta)
   - **Email Responden 1**: `test.konsultasi@example.com`
   - **Email Responden 2**: `responden.kedua.konsultasi@example.com`
4. Klik **"Simpan"**
5. **Verifikasi**: Same checklist as Konstruksi

### Kriteria Success
- Data tersimpan ke database
- Tidak ada error message
- Redirect/refresh ke halaman daftar dengan data baru terlihat

### Kriteria Failed
- Form validation error (field wajib kosong)
- Error message dari server
- Data tidak muncul di tabel setelah refresh

---

## SCENARIO 2: Share Link (Create Share)

### Tujuan
Memastikan fitur share link SIMAK berfungsi untuk membagikan dokumen ke pihak eksternal.

### Prasyarat
- Data SIMAK sudah ada (dari Scenario 1 atau data existing)
- User memiliki permission share

### Langkah Testing

#### Konstruksi (KON-03)
1. Buka halaman SIMAK Konstruksi
2. Cari data yang akan dishare (dari Scenario 1 atau data existing)
3. Klik tombol **Share** (ikon share-alt / tombol biru) pada baris data
4. Modal "Bagikan SIMAK" akan muncul
5. Pilih durasi share:
   - [ ] 1 minggu (default)
   - [ ] 30 hari
6. Klik tombol **"Buat Link Bagikan"**
7. **Verifikasi**:
   - [ ] Link share terbentuk dan ditampilkan
   - [ ] Tombol "Buka Tautan" berfungsi (membuka di tab baru)
   - [ ] Tombol "Salin" berfungsi (copy ke clipboard)
   - [ ] Link mengandung format: `https://satkerpps-riau.online/simak/share/[token]`

#### Konsultasi (KON-05)
1. Buka halaman SIMAK Konsultasi
2. Klik tombol Share pada salah satu data
3. Pilih durasi 30 hari
4. Buat link share
5. **Verifikasi**:
   - [ ] Link terbentuk dengan format benar
   - [ ] Link berbeda dari link konstruksi

### Kriteria Success
- Link share berhasil digenerate
- Link bisa diakses tanpa login (public access)
- Token unik untuk setiap share

### Kriteria Failed
- Link tidak terbentuk
- Link tidak bisa diakses
- Durasi tidak tersimpan dengan benar

---

## SCENARIO 3: Upload Data (Import Excel)

### Tujuan
Memastikan fitur import data dari file Excel berfungsi dengan benar.

### Prasyarat
- User memiliki permission import
- File template Excel sudah didownload

### Langkah Testing

#### Download Template (KON-06)
1. Buka halaman SIMAK Konstruksi
2. Klik tombol **"Import Excel"** (warna hijau)
3. Modal import akan muncul
4. Klik link **"Download Template (XLSX)"**
5. **Verifikasi**:
   - [ ] File terdownload
   - [ ] File berekstensi .xlsx
   - [ ] Template memiliki header kolom yang benar

#### Prepare Test Data (KON-07)
1. Buka file template Excel
2. Isi data test pada baris pertama (kosongkan baris header):
   ```
   | ppk_nip       | nama_paket                  | tahun_anggaran | nomor_kontrak                     | nilai_kontrak |
   |---------------|-----------------------------|----------------|-----------------------------------|---------------|
   | 199012212018021001 | Import Test Konstruksi 2024 | 2024 - 2025    | PLN/SIMAK-IMPORT-KON/[001]/2024  | 750000000     |
   ```
3. Simpan file dengan nama: `test_import_konstruksi_[YYYYMMDD].xlsx`

#### Import Data (KON-08)
1. Buka halaman SIMAK Konstruksi
2. Klik "Import Excel"
3. Pilih file yang sudah disiapkan
4. Klik tombol **"Import"**
5. **Verifikasi**:
   - [ ] Proses import selesai tanpa error
   - [ ] Pesan success muncul
   - [ ] Data baru terlihat di tabel
   - [ ] Nomor kontrak sesuai dengan yang diinput di Excel

#### Konsultasi - Import (KON-09)
1. Buka halaman SIMAK Konsultasi
2. Download template Excel
3. Isi data test konsultansi:
   ```
   | ppk_nip       | nama_paket                  | tahun_anggaran | nomor_kontrak                     | nilai_kontrak |
   |---------------|-----------------------------|----------------|-----------------------------------|---------------|
   | 199012212018021001 | Import Test Konsultasi 2024 | 2024 - 2025    | PLN/SIMAK-IMPORT-KONS/[001]/2024 | 300000000     |
   ```
4. Import file
5. **Verifikasi**: Same checklist as Konstruksi

### Kriteria Success
- File Excel berhasil di-parse
- Data tersimpan ke database dengan benar
- Semua field yang required terisi
- NIP PPK auto-resolved ke nama

### Kriteria Failed
- Error parsing Excel
- Field required tidak ada di Excel
- NIP PPK tidak ditemukan di master pegawai
- Duplicate nomor kontrak

---

## SCENARIO 4: Verifikasi Data (Verifikasi Dokumen)

### Tujuan
Memastikan fitur verifikasi kelengkapan dokumen berfungsi.

### Prasyarat
- Data SIMAK sudah ada
- User memiliki permission verifikasi

### Langkah Testing

#### Akses Halaman Verifikasi (KON-10)
1. Buka halaman SIMAK Konstruksi
2. Klik tombol **"VERIFIKASI"** (warna hijau) pada salah satu data
3. **Verifikasi**:
   - [ ] Redirect ke halaman detail verifikasi
   - [ ] URL berubah ke: `https://satkerpps-riau.online/admin/kontrak/simak/konstruksi/[ID]`
   - [ ] Halaman menampilkan daftar dokumen yang harus diverifikasi

#### Verifikasi Dokumen (KON-11)
1. Di halaman verifikasi, cari salah satu dokumen
2. Pilih status verifikasi:
   - [ ] **Lengkap** (dokumen sudah ada dan sesuai)
   - [ ] **Belum Sesuai** (ada tapi tidak sesuai)
   - [ ] **Menunggu Verifikasi** (belum dicek)
   - [ ] **Belum Ada** (dokumen belum diupload)
3. Klik tombol **Simpan Verifikasi**
4. **Verifikasi**:
   - [ ] Status tersimpan
   - [ ] Persentase kelengkapan berubah
   - [ ] Data terakhir diverifikasi tercatat

#### Upload Dokumen Verifikasi (KON-12)
1. Di halaman verifikasi, cari dokumen dengan status "Belum Ada"
2. Klik tombol **Upload** atau drag-drop file
3. Upload file PDF/JPG/PNG (maks 10MB)
4. **Verifikasi**:
   - [ ] File berhasil diupload
   - [ ] Nama file muncul di list
   - [ ] Preview/thumbnail muncul (untuk gambar)
   - [ ] Status berubah menjadi "Menunggu Verifikasi"

#### Konsultasi - Verifikasi (KON-13)
1. Buka halaman SIMAK Konsultasi
2. Klik tombol VERIFIKASI pada salah satu data
3. Lakukan proses verifikasi serupa
4. **Verifikasi**:
   - [ ] Semua fitur berfungsi sama seperti Konstruksi
   - [ ] Persentase kelengkapan diupdate

### Kriteria Success
- Halaman verifikasi dapat diakses
- Status verifikasi tersimpan
- Upload dokumen berfungsi
- Persentase kelengkapan akurat

### Kriteria Failed
- Halaman verifikasi error 404
- Status tidak tersimpan
- Upload gagal (size/type restriction)
- Persentase tidak berubah setelah update

---

## SCENARIO 5: Edit Data (Update)

### Tujuan
Memastikan fitur edit data SIMAK berfungsi untuk koreksi data.

### Prasyarat
- Data SIMAK sudah ada (dari Scenario 1 atau existing)

### Langkah Testing

#### Edit Data Konstruksi (KON-14)
1. Buka halaman SIMAK Konstruksi
2. Klik tombol **"EDIT"** (warna kuning) pada salah satu data
3. Modal edit akan muncul dengan data yang sudah terisi
4. Ubah beberapa field:
   - **Nama Paket**: Tambahkan suffix ` [EDITED]`
   - **Nilai Kontrak**: Ubah menjadi `600000000`
5. Klik tombol **"Simpan"**
6. **Verifikasi**:
   - [ ] Modal menutup
   - [ ] Data di tabel terupdate
   - [ ] Nilai kontrak baru tercermin

#### Edit Data Konsultasi (KON-15)
1. Buka halaman SIMAK Konsultasi
2. Edit salah satu data
3. Ubah field-field yang diperlukan
4. **Verifikasi**: Same checklist as Konstruksi

### Kriteria Success
- Data dapat diedit
- Perubahan tersimpan
- History edit tercatat (jika applicable)

### Kriteria Failed
- Field tidak bisa diedit
- Perubahan tidak tersimpan
- Error validasi saat save

---

## SCENARIO 6: Export / Download

### Tujuan
Memastikan fitur export data berfungsi.

### Langkah Testing

#### Export Excel (KON-16)
1. Buka halaman SIMAK Konstruksi
2. Cari tombol/link **"Export Excel"** (jika ada di header/footer)
3. Klik export
4. **Verifikasi**:
   - [ ] File terdownload
   - [ ] Format file .xlsx
   - [ ] Data lengkap (semua kolom)

#### Download ZIP Dokumen (KON-17)
1. Buka halaman detail SIMAK Konstruksi (klik VERIFIKASI)
2. Cari tombol **"Download ZIP"** (jika ada)
3. Klik download
4. **Verifikasi**:
   - [ ] File ZIP terdownload
   - [ ] Isi ZIP sesuai dengan dokumen yang ada

### Kriteria Success
- File terdownload dengan benar
- Data akurat dan lengkap

### Kriteria Failed
- Download gagal
- File corrupt
- Data tidak sesuai

---

## Summary Checklist

| ID | Skenario | Halaman | Status |
|----|----------|---------|--------|
| KON-01 | Input Data Baru | Konstruksi | ⬜ |
| KON-02 | Input Data Baru | Konsultasi | ⬜ |
| KON-03 | Create Share Link | Konstruksi | ⬜ |
| KON-05 | Create Share Link | Konsultasi | ⬜ |
| KON-06 | Download Template | Konstruksi | ⬜ |
| KON-07 | Prepare Test Data | Excel | ⬜ |
| KON-08 | Import Excel | Konstruksi | ⬜ |
| KON-09 | Import Excel | Konsultasi | ⬜ |
| KON-10 | Akses Verifikasi | Konstruksi | ⬜ |
| KON-11 | Verifikasi Dokumen | Konstruksi | ⬜ |
| KON-12 | Upload Dokumen | Konstruksi | ⬜ |
| KON-13 | Verifikasi Dokumen | Konsultasi | ⬜ |
| KON-14 | Edit Data | Konstruksi | ⬜ |
| KON-15 | Edit Data | Konsultasi | ⬜ |
| KON-16 | Export Excel | Konstruksi | ⬜ |
| KON-17 | Download ZIP | Konstruksi | ⬜ |

**Total Skenario: 16**

---

## Catatan Testing

### Environment
- Browser: Chrome / Firefox / Safari
- User Role: Admin / Editor
- Session: Login sebagai user dengan akses lengkap

### Test Data Cleanup
Setelah smoke test selesai, pastikan untuk:
1. Hapus data test yang dibuat (jika ada fitur delete)
2. Deactivate share links yang dibuat
3. Catat ID data yang dibuat untuk tracking

### Reporting
Buat laporan dengan format:
```
- PASSED: [list scenario yang berhasil]
- FAILED: [list scenario yang gagal + screenshot error]
- NOTES: [catatan tambahan]
```