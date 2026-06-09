-- ============================================================
-- DELETE SMOKE TEST DATA v2
-- Konstruksi ID: 12   | Nomor: SMOKE/KON/v2/20260609173702
-- Konsultasi  ID: 4  | Nomor: SMOKE/KONS/v2/20260609173702
-- Generated: 20260609173702
-- ============================================================

-- 1. Konstruksi
DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = 12;
DELETE FROM trn_kontrak_simak_share WHERE simak_id = 12;
DELETE FROM trn_kontrak_simak WHERE id = 12 AND nomor_kontrak = 'SMOKE/KON/v2/20260609173702';

-- 2. Konsultasi
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = 4;
DELETE FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = 4;
DELETE FROM trn_kontrak_simak_konsultasi WHERE id = 4 AND nomor_kontrak = 'SMOKE/KONS/v2/20260609173702';

-- Verifikasi (semua harus 0):
SELECT 'trn_kontrak_simak' AS tabel, COUNT(*) AS sisa
  FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/v2/20260609173702'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*)
  FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/v2/20260609173702';
