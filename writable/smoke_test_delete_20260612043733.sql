-- ============================================================
-- DELETE SMOKE TEST DATA
-- Konstruksi ID: 25  |  Nomor: SMOKE/KON/1/20260612043733
-- Konsultasi ID: 20  |  Nomor: SMOKE/KONS/1/20260612043733
-- Generated: 20260612043733
-- ============================================================

-- 1. Delete Konstruksi verification documents
DELETE FROM trn_kontrak_simak_verifikasi_dokumen
WHERE simak_id = 25;

-- 2. Delete Konstruksi share links
DELETE FROM trn_kontrak_simak_share
WHERE simak_id = 25;

-- 3. Delete Konstruksi contract record
DELETE FROM trn_kontrak_simak
WHERE id = 25
  AND nomor_kontrak = 'SMOKE/KON/1/20260612043733';

-- 4. Delete Konsultasi verification documents
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen
WHERE simak_id = 20;

-- 5. Delete Konsultasi share links
DELETE FROM trn_kontrak_simak_konsultasi_share
WHERE simak_id = 20;

-- 6. Delete Konsultasi contract record
DELETE FROM trn_kontrak_simak_konsultasi
WHERE id = 20
  AND nomor_kontrak = 'SMOKE/KONS/1/20260612043733';

-- Verify nothing remains:
SELECT 'trn_kontrak_simak' AS tbl, COUNT(*) AS remaining FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/1/20260612043733'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*) FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/1/20260612043733';
