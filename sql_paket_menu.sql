-- =====================================================
-- SQL untuk Menu Master Paket
-- =====================================================

-- Jika php spark migrate tidak mau jalan,
-- hapus record migration lama ini dari tabel migrations:

DELETE FROM migrations WHERE name LIKE '%AddMasterPaketSubmenu%';
DELETE FROM migrations WHERE name LIKE '%InsertMasterPaketMenu%';

-- Lalu jalankan:
php spark migrate

-- Atau jalankan SQL ini langsung di phpMyAdmin:

-- =====================================================
-- LANGKAH 1: Cek ID menu_lv1 master
-- =====================================================
SELECT id FROM menu_lv1 WHERE LOWER(label) = 'master';
-- Contoh hasil: master-01


-- =====================================================
-- LANGKAH 2: Insert menu Paket
-- =====================================================
INSERT INTO menu_lv2 (id, label, icon, link, header, ordering)
VALUES ('master-10', 'Paket', 'far fa-circle', 'admin/master/paket', 'master-01', 10);


-- =====================================================
-- LANGKAH 3: Insert akses menu
-- =====================================================
INSERT INTO menu_akses (role_id, menu_id, FiturAdd, FiturEdit, FiturDelete, FiturExport, FiturImport, FiturApproval)
VALUES (1, 'master-10', 1, 1, 0, 0, 0, 0);


-- =====================================================
-- LANGKAH 4: Verifikasi
-- =====================================================
SELECT * FROM menu_lv2 WHERE LOWER(label) = 'paket';
SELECT * FROM menu_akses WHERE menu_id = 'master-10';
