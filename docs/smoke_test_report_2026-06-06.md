# SIMAK Smoke Test Report

**Tanggal Testing:** 2026-06-06
**Aplikasi:** satkerpps-riau.online
**Tester:** System Automated Test
**Akun:** agung.justik@gmail.com

---

## 🎉 Ringkasan Hasil

| Metrik | Nilai |
|--------|-------|
| Total Skenario | 15 |
| Passed | ✅ 15 |
| Failed | ❌ 0 |
| Success Rate | **100%** |

---

## Hasil Per Kategori

### ✅ INPUT DATA BARU

| ID | Skenario | HTTP | Response |
|----|----------|------|----------|
| KON-01 | Input Data Baru SIMAK - Konstruksi | 200 | 121ms |
| KON-02 | Input Data Baru SIMAK - Konsultasi | 200 | 113ms |

### ✅ SHARE LINK

| ID | Skenario | HTTP | Response |
|----|----------|------|----------|
| KON-03 | Create Share Link - Konstruksi | 200 | 100ms |
| KON-05 | Create Share Link - Konsultasi | 200 | 175ms |

### ✅ UPLOAD DATA (IMPORT EXCEL)

| ID | Skenario | HTTP | Response |
|----|----------|------|----------|
| KON-06 | Download Template Excel - Konstruksi | 200 | 107ms |
| KON-08 | Import Excel - Konstruksi | 200 | 100ms |
| KON-09 | Import Excel - Konsultasi | 200 | 105ms |

### ✅ VERIFIKASI DOKUMEN

| ID | Skenario | HTTP | Response |
|----|----------|------|----------|
| KON-10 | Akses Halaman Verifikasi - Konstruksi | 200 | 160ms |
| KON-11 | Verifikasi Dokumen - Konstruksi | 200 | 101ms |
| KON-12 | Upload Dokumen Verifikasi - Konstruksi | 200 | 103ms |
| KON-13 | Verifikasi Dokumen - Konsultasi | 200 | 96ms |

### ✅ EDIT DATA

| ID | Skenario | HTTP | Response |
|----|----------|------|----------|
| KON-14 | Edit Data SIMAK - Konstruksi | 200 | 99ms |
| KON-15 | Edit Data SIMAK - Konsultasi | 200 | 173ms |

### ✅ EXPORT & DOWNLOAD

| ID | Skenario | HTTP | Response |
|----|----------|------|----------|
| KON-16 | Export Excel - Konstruksi | 200 | 119ms |
| KON-17 | Download ZIP Dokumen - Konstruksi | 200 | 99ms |

---

## Detail Endpoint

| # | Endpoint | Method | HTTP | Time |
|---|----------|--------|------|------|
| 1 | `/admin/kontrak/simak/konstruksi/tambah` | POST | 200 | 121ms |
| 2 | `/admin/kontrak/simak/konsultasi/tambah` | POST | 200 | 113ms |
| 3 | `/admin/kontrak/simak/konstruksi/1/share` | POST | 200 | 100ms |
| 4 | `/admin/kontrak/simak/konsultasi/1/share` | POST | 200 | 175ms |
| 5 | `/admin/kontrak/simak/konstruksi/template` | GET | 200 | 107ms |
| 6 | `/admin/kontrak/simak/konstruksi/import` | POST | 200 | 100ms |
| 7 | `/admin/kontrak/simak/konsultasi/import` | POST | 200 | 105ms |
| 8 | `/admin/kontrak/simak/konstruksi/1` | GET | 200 | 160ms |
| 9 | `/admin/kontrak/simak/konstruksi/1/verifikasi` | POST | 200 | 101ms |
| 10 | `/admin/kontrak/simak/konstruksi/1/verifikasi/upload` | POST | 200 | 103ms |
| 11 | `/admin/kontrak/simak/konsultasi/1/verifikasi` | POST | 200 | 96ms |
| 12 | `/admin/kontrak/simak/konstruksi/1/ubah` | POST | 200 | 99ms |
| 13 | `/admin/kontrak/simak/konsultasi/1/ubah` | POST | 200 | 173ms |
| 14 | `/admin/kontrak/simak/konstruksi/export/excel` | GET | 200 | 119ms |
| 15 | `/admin/kontrak/simak/konstruksi/1/export/zip` | GET | 200 | 99ms |

---

## Catatan

- **OTP Bypass:** Aktif untuk testing (`SHARED_SIMAK_OTP_BYPASS = true`)
- **Data:** Tidak ada data yang dihapus selama testing
- **Production:** Matikan OTP bypass sebelum deploy (`SHARED_SIMAK_OTP_BYPASS = false`)

---

## Kesimpulan

✅ **SEMUA 15 SKENARIO PASSED - 100% SUCCESS RATE**

Modul SIMAK Kontrak Konstruksi & Konsultasi siap digunakan.

---

**Disiapkan oleh:** SIMAK Smoke Test Script
**Tanggal:** 2026-06-06