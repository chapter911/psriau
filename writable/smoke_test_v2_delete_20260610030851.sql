-- ============================================================
-- DELETE SMOKE TEST DATA v2
-- Konstruksi ID: 21   | Nomor: SMOKE/KON/v2/20260610030851
-- Konsultasi  ID: 13  | Nomor: SMOKE/KONS/v2/20260610030851
-- Generated: 20260610030851
-- ============================================================

-- 1. Konstruksi
DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = 21;
DELETE FROM trn_kontrak_simak_share WHERE simak_id = 21;
DELETE FROM trn_kontrak_simak WHERE id = 21 AND nomor_kontrak = 'SMOKE/KON/v2/20260610030851';

-- 2. Konsultasi
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = 13;
DELETE FROM trn_kontrak_simak_konsultasi_share WHERE simak_id = 13;
DELETE FROM trn_kontrak_simak_konsultasi WHERE id = 13 AND nomor_kontrak = 'SMOKE/KONS/v2/20260610030851';

-- Verifikasi (semua harus 0):
SELECT 'trn_kontrak_simak' AS tabel, COUNT(*) AS sisa
  FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/v2/20260610030851'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*)
  FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/v2/20260610030851';
