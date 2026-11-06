<?php

namespace App\Models;

use CodeIgniter\Model;

class SwakelolaPerjadinPersonelModel extends Model
{
    protected $table            = 'trn_swakelola_perjadin_personel';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'paket_id',
        'realisasi_id',
        'kode_paket',
        'nama_paket',
        'kode_akun',
        'nomor_spt',
        'tanggal_spt',
        'nomor_spd',
        'nama_personel',
        'nip',
        'jabatan',
        'instansi',
        'golongan',
        'pegawai_id',
        'is_matched_master',
        'kota_tujuan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'lama_hari',
        'maksud_tugas',
        'nomor_spm',
        'tanggal_spm',
        'nomor_sp2d',
        'nilai_biaya',
        'file_bukti',
        'keterangan',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get unique employees listed in swakelola perjadin
     */
    public function getUniquePersonel(): array
    {
        return $this->select('nama_personel, nip, is_matched_master, pegawai_id, COUNT(*) as total_perjalanan, SUM(nilai_biaya) as total_biaya')
            ->groupBy('nama_personel, nip, is_matched_master, pegawai_id')
            ->orderBy('total_perjalanan', 'DESC')
            ->findAll();
    }

    /**
     * Get unique destinations
     */
    public function getUniqueDestinations(): array
    {
        $rows = $this->select('kota_tujuan')
            ->distinct()
            ->where('kota_tujuan IS NOT NULL')
            ->orderBy('kota_tujuan', 'ASC')
            ->findAll();

        return array_column($rows, 'kota_tujuan');
    }

    /**
     * Get filtered records with joined package & master pegawai details
     */
    public function getFilteredPersonel(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->select('trn_swakelola_perjadin_personel.*');

        $db = $this->db;

        if ($db->tableExists('trn_swakelola_paket')) {
            $builder->select('p.pic_nama, p.pic_label, p.lha_no, p.slug as paket_slug');
            $builder->join('trn_swakelola_paket p', 'p.id = trn_swakelola_perjadin_personel.paket_id', 'left');
        }

        if ($db->tableExists('mst_pegawai')) {
            $builder->select('peg.nama as master_nama, peg.nip as master_nip, peg.golongan as master_golongan');
            if ($db->tableExists('mst_jabatan')) {
                $builder->select('ju.jabatan as master_jabatan');
                $builder->join('mst_pegawai peg', 'peg.id = trn_swakelola_perjadin_personel.pegawai_id', 'left');
                $builder->join('mst_jabatan ju', 'ju.id = peg.jabatan_utama_id', 'left');
            } else {
                $builder->join('mst_pegawai peg', 'peg.id = trn_swakelola_perjadin_personel.pegawai_id', 'left');
            }
        }

        if (! empty($filters['paket_id'])) {
            $builder->where('trn_swakelola_perjadin_personel.paket_id', (int) $filters['paket_id']);
        }

        if (! empty($filters['nama_personel'])) {
            $builder->like('trn_swakelola_perjadin_personel.nama_personel', trim($filters['nama_personel']));
        }

        if (! empty($filters['kota_tujuan'])) {
            $builder->like('trn_swakelola_perjadin_personel.kota_tujuan', trim($filters['kota_tujuan']));
        }

        if (! empty($filters['status_match'])) {
            if ($filters['status_match'] === 'matched') {
                $builder->where('trn_swakelola_perjadin_personel.is_matched_master', 1);
            } elseif ($filters['status_match'] === 'unmatched') {
                $builder->where('trn_swakelola_perjadin_personel.is_matched_master', 0);
            }
        }

        if (! empty($filters['spm'])) {
            $builder->like('trn_swakelola_perjadin_personel.nomor_spm', trim($filters['spm']));
        }

        if (! empty($filters['kode_akun'])) {
            $builder->where('trn_swakelola_perjadin_personel.kode_akun', trim($filters['kode_akun']));
        }

        $builder->orderBy('trn_swakelola_perjadin_personel.tanggal_berangkat', 'DESC');
        $builder->orderBy('trn_swakelola_perjadin_personel.nomor_spt', 'ASC');
        $builder->orderBy('trn_swakelola_perjadin_personel.nama_personel', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Compute summary statistics for KPI cards
     */
    public function getSummaryStats(array $filters = []): array
    {
        $all = $this->getFilteredPersonel($filters);

        $totalPerjalanan = count($all);
        $totalBiaya      = 0.0;
        $matchedCount    = 0;
        $unmatchedCount  = 0;
        $uniquePersonel  = [];

        foreach ($all as $row) {
            $totalBiaya += (float) ($row['nilai_biaya'] ?? 0);
            if ((int) ($row['is_matched_master'] ?? 0) === 1) {
                $matchedCount++;
            } else {
                $unmatchedCount++;
            }
            $cleanKey = strtolower(trim((string) ($row['nama_personel'] ?? '')));
            if ($cleanKey !== '') {
                $uniquePersonel[$cleanKey] = true;
            }
        }

        return [
            'total_perjalanan' => $totalPerjalanan,
            'total_biaya'      => $totalBiaya,
            'personel_unik'    => count($uniquePersonel),
            'matched'          => $matchedCount,
            'unmatched'        => $unmatchedCount,
        ];
    }

    /**
     * Synchronize and match all Perjadin records with mst_pegawai
     */
    public function syncWithMasterPegawai(): array
    {
        $db = $this->db;
        if (! $db->tableExists('trn_swakelola_perjadin_personel') || ! $db->tableExists('mst_pegawai')) {
            return ['total' => 0, 'matched' => 0, 'unmatched' => 0];
        }

        $pegBuilder = $db->table('mst_pegawai')
            ->select('mst_pegawai.id, mst_pegawai.nama, mst_pegawai.nip, mst_pegawai.golongan');
        if ($db->tableExists('mst_jabatan')) {
            $pegBuilder->select('ju.jabatan as jabatan_label')
                ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left');
        }

        $pegList = $pegBuilder->get()->getResultArray();

        // Index by clean NIP & normalized name
        $nipMap  = [];
        $nameMap = [];

        foreach ($pegList as $peg) {
            $rawNip = trim((string) ($peg['nip'] ?? ''));
            $cleanNip = preg_replace('/\D/', '', $rawNip);
            if (strlen($cleanNip) >= 8) {
                $nipMap[$cleanNip] = $peg;
            }

            $normName = $this->normalizePersonName((string) ($peg['nama'] ?? ''));
            if ($normName !== '') {
                $nameMap[$normName] = $peg;
            }
        }

        $perjadinRows = $db->table('trn_swakelola_perjadin_personel')->get()->getResultArray();
        $matched   = 0;
        $unmatched = 0;
        $now       = date('Y-m-d H:i:s');

        foreach ($perjadinRows as $row) {
            $rowId = (int) $row['id'];
            $nip   = trim((string) ($row['nip'] ?? ''));
            $name  = trim((string) ($row['nama_personel'] ?? ''));

            $cleanNip  = preg_replace('/\D/', '', $nip);
            $normName  = $this->normalizePersonName($name);
            $matchedPeg = null;

            // 1. Try matching by NIP
            if (strlen($cleanNip) >= 8 && isset($nipMap[$cleanNip])) {
                $matchedPeg = $nipMap[$cleanNip];
            }

            // 2. Try exact normalized name
            if ($matchedPeg === null && isset($nameMap[$normName])) {
                $matchedPeg = $nameMap[$normName];
            }

            // 3. Try fuzzy/contains match on name
            if ($matchedPeg === null) {
                foreach ($nameMap as $kName => $peg) {
                    if (str_contains($normName, $kName) || str_contains($kName, $normName)) {
                        $matchedPeg = $peg;
                        break;
                    }
                }
            }

            if ($matchedPeg !== null) {
                $matched++;
                $updateData = [
                    'pegawai_id'        => (int) $matchedPeg['id'],
                    'is_matched_master' => 1,
                    'updated_at'        => $now,
                ];
                if (empty($row['golongan']) && ! empty($matchedPeg['golongan'])) {
                    $updateData['golongan'] = $matchedPeg['golongan'];
                }
                if (empty($row['jabatan']) && ! empty($matchedPeg['jabatan_label'])) {
                    $updateData['jabatan'] = $matchedPeg['jabatan_label'];
                }
                $db->table('trn_swakelola_perjadin_personel')->where('id', $rowId)->update($updateData);
            } else {
                $unmatched++;
                $db->table('trn_swakelola_perjadin_personel')->where('id', $rowId)->update([
                    'pegawai_id'        => null,
                    'is_matched_master' => 0,
                    'updated_at'        => $now,
                ]);
            }
        }

        return [
            'total'     => count($perjadinRows),
            'matched'   => $matched,
            'unmatched' => $unmatched,
        ];
    }

    /**
     * Clean and normalize person name for fuzzy comparison
     */
    private function normalizePersonName(string $name): string
    {
        $clean = mb_strtolower($name, 'UTF-8');
        // Remove academic degrees, professional titles, prefixes
        $clean = preg_replace('/\b(dr|dra|drs|ir|prof|h|hj|kh)\b\.?/i', '', $clean);
        $clean = preg_replace('/,\s*(s\.t\.|st|s\.ars|s\.si|s\.ds|s\.sos|s\.e\.|se|a\.md|amd|m\.t\.|mt|s\.st|m\.si|m\.m\.|mm)\.?/i', '', $clean);
        $clean = preg_replace('/\b(s\.t\.|st|s\.ars|s\.si|s\.ds|s\.sos|s\.e\.|se|a\.md|amd|m\.t\.|mt|s\.st)\b/i', '', $clean);
        // Remove punctuation and extra whitespace
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return trim($clean);
    }
}
