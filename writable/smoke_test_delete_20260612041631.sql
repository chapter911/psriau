-- ============================================================
-- DELETE SMOKE TEST DATA
-- Konstruksi ID: 23  |  Nomor: SMOKE/KON/1/20260612041631
-- Konsultasi ID: 18  |  Nomor: SMOKE/KONS/1/20260612041631
-- Generated: 20260612041631
-- ============================================================

-- 1. Delete Konstruksi verification documents
DELETE FROM trn_kontrak_simak_verifikasi_dokumen
WHERE simak_id = 23;

-- 2. Delete Konstruksi share links
DELETE FROM trn_kontrak_simak_share
WHERE simak_id = 23;

-- 3. Delete Konstruksi contract record
DELETE FROM trn_kontrak_simak
WHERE id = 23
  AND nomor_kontrak = 'SMOKE/KON/1/20260612041631';

-- 4. Delete Konsultasi verification documents
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen
WHERE simak_id = 18;

-- 5. Delete Konsultasi share links
DELETE FROM trn_kontrak_simak_konsultasi_share
WHERE simak_id = 18;

-- 6. Delete Konsultasi contract record
DELETE FROM trn_kontrak_simak_konsultasi
WHERE id = 18
  AND nomor_kontrak = 'SMOKE/KONS/1/20260612041631';

-- Verify nothing remains:
SELECT 'trn_kontrak_simak' AS tbl, COUNT(*) AS remaining FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/1/20260612041631'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*) FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/1/20260612041631';
