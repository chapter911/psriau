-- ============================================================
-- DELETE SMOKE TEST DATA v2
-- Konstruksi ID: 11   | Nomor: SMOKE/KON/v2/20260609173026
-- Konsultasi  ID: 3  | Nomor: SMOKE/KONS/v2/20260609173026
-- Generated: 20260609173026
-- ============================================================

-- 1. Konstruksi
DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = 11;
DELETE FROM trn_kontrak_simak_share WHERE simak_id = 11;
DELETE FROM trn_kontrak_simak WHERE id = 11 AND nomor_kontrak = 'SMOKE/KON/v2/20260609173026';

-- 2. Konsultasi
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = 3;
DELETE FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = 3;
DELETE FROM trn_kontrak_simak_konsultasi WHERE id = 3 AND nomor_kontrak = 'SMOKE/KONS/v2/20260609173026';

-- Verifikasi (semua harus 0):
SELECT 'trn_kontrak_simak' AS tabel, COUNT(*) AS sisa
  FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/v2/20260609173026'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*)
  FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/v2/20260609173026';
