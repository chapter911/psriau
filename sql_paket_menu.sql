-- =====================================================
-- SQL untuk Menu Master Paket
-- =====================================================

-- FILE YANG DIBUAT:
-- 1. app/Database/Migrations/2026-06-27-000001_CreateMstPaket.php (tabel mst_paket)
-- 2. app/Database/Migrations/2026-06-27-000002_AddMasterPaketSubmenu.php (menu sidebar)
-- 3. app/Models/MstPaketModel.php
-- 4. app/Controllers/Admin/Paket.php
-- 5. app/Views/admin/master/paket.php
-- 6. app/Config/Routes.php (routes sudah ditambahkan)
-- 7. sql_paket_menu.sql (file ini)

-- =====================================================
-- LANGKAH-LANGKAH SETELAH FILE DIBUAT:
-- =====================================================

-- 1. Jalankan migrasi untuk membuat tabel dan menu:
php spark migrate

-- 2. Jika migrasi gagal, jalankan SQL manual berikut:


-- =====================================================
-- A. CREATE TABLE mst_paket
-- =====================================================
CREATE TABLE IF NOT EXISTS `mst_paket` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_paket` varchar(255) NOT NULL,
  `singkatan_paket` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =====================================================
-- B. INSERT MENU KE menu_lv3
-- (Ambil header_id dari menu_lv2 yang berlabel 'master')
-- =====================================================
-- Ganti 'master-0X' dengan id menu_lv2 yang berlabel 'master'
-- Contoh: INSERT INTO menu_lv3 (id, label, icon, link, header, ordering)
-- VALUES ('master-XX', 'Paket', 'far fa-dot-circle', 'admin/master/paket', 'master-01', 99);


-- =====================================================
-- C. INSERT AKSES MENU (opsional - dilakukan oleh migration)
-- =====================================================
-- INSERT INTO menu_akses (role_id, menu_id, FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval)
-- VALUES (1, 'menu_id_dari_menu_lv3', 1, 1, 0, 0, 0, 0);
