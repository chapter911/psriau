# Website Profil Satker PPS

Proyek ini adalah aplikasi web berbasis CodeIgniter 4 untuk mengelola konten profil instansi, informasi acara, berita/artikel, serta panel admin untuk manajemen data.

## Ringkasan Fitur

- Halaman publik: beranda, tentang, daftar acara, dan daftar berita/artikel.
- Panel admin untuk manajemen konten dan pengaturan tampilan.
- Sistem autentikasi berbasis session dan role.
- Pengaturan menu serta kontrol akses berbasis menu.
- Riwayat aktivitas admin (login, edit, delete) untuk kebutuhan audit.

## Teknologi

- PHP 8+
- CodeIgniter 4
- MySQL/MariaDB
- AdminLTE (UI admin)

## Struktur Direktori Utama

- `app/Controllers`: controller aplikasi
- `app/Models`: model database
- `app/Views`: tampilan publik dan admin
- `app/Database`: migration dan seeder
- `public/`: web root dan aset publik
- `writable/`: cache, logs, dan session runtime

## Menjalankan Proyek (Lokal)

1. Install dependency:
   - `composer install`

2. Siapkan konfigurasi environment:
   - Salin `.env.example` menjadi `.env` (jika tersedia), atau gunakan `.env` sesuai kebutuhan.
   - Isi konfigurasi database dan base URL melalui `.env`.

3. Jalankan migration:
   - `php spark migrate`

4. (Opsional) Jalankan seeder bila diperlukan:
   - `php spark db:seed DatabaseSeeder`

5. Jalankan server development:
   - `php spark serve`

6. Akses aplikasi:
   - Website: `http://localhost:8080`
   - Login admin: `http://localhost:8080/masuk`

## Keamanan

- Jangan simpan kredensial (username/password/token/API key) di repository.
- Jangan menuliskan data sensitif pada README, source code, atau file yang ter-commit.
- Gunakan password kuat dan ubah kredensial awal saat deployment.
- Batasi akses database menggunakan prinsip least privilege.

## Catatan

Jika terdapat error ekstensi PHP (misalnya `intl`), pastikan ekstensi yang dibutuhkan CodeIgniter sudah aktif pada lingkungan PHP yang digunakan.
