-- ============================================================
-- DELETE SMOKE TEST DATA v2
-- Konstruksi ID: 31   | Nomor: SMOKE/KON/v2/20260615023250
-- Konsultasi  ID: 25  | Nomor: SMOKE/KONS/v2/20260615023250
-- Generated: 20260615023250
-- ============================================================

-- 1. Konstruksi
DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = 31;
DELETE FROM trn_kontrak_simak_share WHERE simak_id = 31;
DELETE FROM trn_kontrak_simak WHERE id = 31 AND nomor_kontrak = 'SMOKE/KON/v2/20260615023250';

-- 2. Konsultasi
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = 25;
DELETE FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = 25;
DELETE FROM trn_kontrak_simak_konsultasi WHERE id = 25 AND nomor_kontrak = 'SMOKE/KONS/v2/20260615023250';

-- Verifikasi (semua harus 0):
SELECT 'trn_kontrak_simak' AS tabel, COUNT(*) AS sisa
  FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/v2/20260615023250'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*)
  FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/v2/20260615023250';
