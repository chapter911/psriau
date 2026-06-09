-- ============================================================
-- DELETE SMOKE TEST DATA
-- Konstruksi ID: 9   | Nomor: SMOKE/KON/1/20260609171843
-- Konsultasi ID: 2  | Nomor: SMOKE/KONS/1/20260609171843
-- ============================================================

-- 1. Delete Konstruksi verification documents
DELETE FROM trn_kontrak_simak_verifikasi_dokumen
WHERE simak_id = 9;

-- 2. Delete Konstruksi share links
DELETE FROM trn_kontrak_simak_share
WHERE simak_id = 9;

-- 3. Delete Konstruksi contract
DELETE FROM trn_kontrak_simak
WHERE id = 9
  AND nomor_kontrak = 'SMOKE/KON/1/20260609171843';

-- 4. Delete Konsultasi verification documents
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen
WHERE simak_id = 2;

-- 5. Delete Konsultasi share links
DELETE FROM trn_kontrak_simak_konsultasi_share
WHERE simak_id = 2;

-- 6. Delete Konsultasi contract
DELETE FROM trn_kontrak_simak_konsultasi
WHERE id = 2
  AND nomor_kontrak = 'SMOKE/KONS/1/20260609171843';

-- Verify cleanup:
SELECT 'trn_kontrak_simak' AS tbl, COUNT(*) AS remaining
  FROM trn_kontrak_simak WHERE nomor_kontrak = 'SMOKE/KON/1/20260609171843'
UNION ALL
SELECT 'trn_kontrak_simak_konsultasi', COUNT(*)
  FROM trn_kontrak_simak_konsultasi WHERE nomor_kontrak = 'SMOKE/KONS/1/20260609171843';
