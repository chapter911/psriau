-- ============================================================
-- DELETE SMOKE TEST DATA
-- Konstruksi ID: 27  |  Nomor: SMOKE/KON/1/20260612044332
-- Konsultasi ID: 22  |  Nomor: SMOKE/KONS/1/20260612044332
-- Generated: 20260612044332
-- ============================================================

-- 1. Delete Konstruksi verification documents
DELETE FROM trn_kontrak_simak_verifikasi_dokumen
WHERE simak_id = 27;

-- 2. Delete Konstruksi share links
DELETE FROM trn_kontrak_simak_share
WHERE simak_id = 27;

-- 3. Delete Konstruksi contract record
DELETE FROM trn_kontrak_simak
WHERE id = 27
  AND nomor_kontrak = 'SMOKE/KON/1/20260612044332';

-- 4. Delete Konsultasi verification documents
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen
WHERE simak_id = 22;

-- 5. Delete Konsultasi share links
DELETE FROM trn_kontrak_simak_konsultasi_share
WHERE simak_id = 22;

-- 6. Delete Konsultasi contract record
DELETE FROM trn_kontrak_simak_konsultasi
WHERE id = 22
  AND nomor_kontrak = 'SMOKE/KONS/1/20260612044332';

-- Verify nothing remains:
SELECT 'trn_kontrak_simak' AS tbl, COUNT(*) AS remaining FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/1/20260612044332'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*) FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/1/20260612044332';
