<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisAuditItemModel extends Model
{
    protected $table            = 'trn_inventaris_audit_item';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'audit_id',
        'inventaris_id',
        'kode_barang',
        'nup',
        'nama_barang',
        'merk_tipe',
        'satuan',
        'peruntukan',
        'ruangan_sistem_id',
        'ruangan_sistem_nama',
        'kondisi_sistem',
        'status_pinjam_sistem',
        'peminjam_nama',
        'no_surat_pinjam',
        'status_audit',
        'kondisi_fisik',
        'ruangan_fisik_id',
        'ruangan_fisik_nama',
        'catatan_pemeriksaan',
        'audited_at',
        'audited_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil item audit dengan filter status, ruangan, pencarian, dan sorting
     */
    public function getItemsByAudit(int $auditId, array $filters = []): array
    {
        $builder = $this->where('audit_id', $auditId);

        if (! empty($filters['status_audit']) && $filters['status_audit'] !== 'semua') {
            $builder->where('status_audit', $filters['status_audit']);
        }

        if (! empty($filters['status_pinjam']) && $filters['status_pinjam'] !== 'semua') {
            $builder->where('status_pinjam_sistem', $filters['status_pinjam']);
        }

        if (isset($filters['ruangan_id']) && $filters['ruangan_id'] !== 'semua' && $filters['ruangan_id'] !== 'all' && $filters['ruangan_id'] !== '') {
            if ($filters['ruangan_id'] === 'non_ruangan') {
                $builder->groupStart()
                    ->where('ruangan_sistem_id IS NULL', null, false)
                    ->orWhere('ruangan_sistem_id', 0)
                    ->groupEnd();
            } elseif ($filters['ruangan_id'] === 'dipinjam') {
                $builder->where('status_pinjam_sistem', 'dipinjam');
            } elseif (is_numeric($filters['ruangan_id'])) {
                $builder->where('ruangan_sistem_id', (int) $filters['ruangan_id']);
            }
        }

        if (! empty($filters['keyword'])) {
            $kw = $filters['keyword'];
            $builder->groupStart()
                ->like('nama_barang', $kw)
                ->orLike('kode_barang', $kw)
                ->orLike('nup', $kw)
                ->orLike('peminjam_nama', $kw)
                ->orLike('ruangan_sistem_nama', $kw)
                ->groupEnd();
        }

        return $builder->orderBy('status_audit = "belum_diperiksa"', 'DESC', false)
            ->orderBy('kode_barang', 'ASC')
            ->orderBy('CAST(NULLIF(nup, "") AS UNSIGNED)', 'ASC', false)
            ->orderBy('nup', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil statistik ringkasan per ruangan dalam suatu sesi audit
     */
    public function getRoomStatsByAudit(int $auditId): array
    {
        $db = db_connect();

        $stats = $db->table($this->table)
            ->select('
                ruangan_sistem_id,
                ruangan_sistem_nama,
                COUNT(id) AS total_item,
                SUM(CASE WHEN status_audit = "sesuai" THEN 1 ELSE 0 END) AS total_sesuai,
                SUM(CASE WHEN status_audit = "terkonfirmasi_dipinjam" OR status_pinjam_sistem = "dipinjam" THEN 1 ELSE 0 END) AS total_dipinjam,
                SUM(CASE WHEN status_audit IN ("kondisi_berubah", "salah_lokasi") THEN 1 ELSE 0 END) AS total_berubah,
                SUM(CASE WHEN status_audit = "tidak_ditemukan" THEN 1 ELSE 0 END) AS total_selisih,
                SUM(CASE WHEN status_audit = "belum_diperiksa" THEN 1 ELSE 0 END) AS total_belum
            ')
            ->where('audit_id', $auditId)
            ->groupBy('ruangan_sistem_id, ruangan_sistem_nama')
            ->orderBy('ruangan_sistem_nama', 'ASC')
            ->get()
            ->getResultArray();

        $ruanganMap = [];
        if ($db->tableExists('mst_ruangan')) {
            $ruangans = $db->table('mst_ruangan')
                ->select('id, kode_ruangan, nama_ruangan, penanggung_jawab_nama, penanggung_jawab_nip, lokasi_lantai')
                ->get()
                ->getResultArray();
            foreach ($ruangans as $r) {
                $ruanganMap[(int) $r['id']] = $r;
            }
        }

        $result = [];
        foreach ($stats as $row) {
            $rId = ! empty($row['ruangan_sistem_id']) ? (int) $row['ruangan_sistem_id'] : null;
            $rNama = $row['ruangan_sistem_nama'] ?: ($rId === null ? 'Gudang / Tanpa Ruangan' : 'Ruangan #' . $rId);
            $rInfo = $rId && isset($ruanganMap[$rId]) ? $ruanganMap[$rId] : null;

            $tot = (int) ($row['total_item'] ?? 0);
            $blm = (int) ($row['total_belum'] ?? 0);
            $selesai = $tot - $blm;
            $pct = $tot > 0 ? round(($selesai / $tot) * 100) : 0;

            $result[] = [
                'ruangan_id'             => $rId,
                'ruangan_key'            => $rId !== null ? (string) $rId : 'non_ruangan',
                'nama_ruangan'           => $rInfo ? $rInfo['nama_ruangan'] : $rNama,
                'kode_ruangan'           => $rInfo ? $rInfo['kode_ruangan'] : '',
                'penanggung_jawab_nama'  => $rInfo ? $rInfo['penanggung_jawab_nama'] : null,
                'penanggung_jawab_nip'   => $rInfo ? $rInfo['penanggung_jawab_nip'] : null,
                'lokasi_lantai'          => $rInfo ? $rInfo['lokasi_lantai'] : null,
                'total_item'             => $tot,
                'total_sesuai'           => (int) ($row['total_sesuai'] ?? 0),
                'total_dipinjam'         => (int) ($row['total_dipinjam'] ?? 0),
                'total_berubah'          => (int) ($row['total_berubah'] ?? 0),
                'total_selisih'          => (int) ($row['total_selisih'] ?? 0),
                'total_belum'            => $blm,
                'total_selesai'          => $selesai,
                'persen'                 => $pct,
            ];
        }

        // Urutkan alfabet nama ruangan, non-ruangan ditaruh di paling akhir
        usort($result, static function ($a, $b) {
            if ($a['ruangan_id'] === null) {
                return 1;
            }
            if ($b['ruangan_id'] === null) {
                return -1;
            }
            return strcmp((string) $a['nama_ruangan'], (string) $b['nama_ruangan']);
        });

        return $result;
    }

    /**
     * Menandai sisa barang pada ruangan tertentu menjadi Sesuai
     */
    public function markRuanganRemainingSesuai(int $auditId, string $ruanganKey, int $auditorId, string $auditorNama): int
    {
        $db = db_connect();
        $builder = $db->table($this->table)
            ->where('audit_id', $auditId)
            ->where('status_audit', 'belum_diperiksa');

        if ($ruanganKey === 'non_ruangan') {
            $builder->groupStart()
                ->where('ruangan_sistem_id IS NULL', null, false)
                ->orWhere('ruangan_sistem_id', 0)
                ->groupEnd();
        } elseif (is_numeric($ruanganKey)) {
            $builder->where('ruangan_sistem_id', (int) $ruanganKey);
        }

        $items = $builder->get()->getResultArray();
        $updatedCount = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($items as $item) {
            $isLoaned = ($item['status_pinjam_sistem'] === 'dipinjam');
            $newStatus = $isLoaned ? 'terkonfirmasi_dipinjam' : 'sesuai';
            $catatan = $isLoaned 
                ? 'Terkonfirmasi sedang dipinjam pakai oleh ' . ($item['peminjam_nama'] ?? 'pegawai') 
                : 'Pemeriksaan fisik per ruangan: Sesuai & ada di lokasi';

            $db->table($this->table)->where('id', $item['id'])->update([
                'status_audit'        => $newStatus,
                'kondisi_fisik'       => $item['kondisi_sistem'] ?: 'Baik',
                'ruangan_fisik_id'    => $item['ruangan_sistem_id'],
                'ruangan_fisik_nama'  => $item['ruangan_sistem_nama'],
                'catatan_pemeriksaan' => $catatan,
                'audited_at'          => $now,
                'audited_by'          => $auditorId,
                'updated_at'          => $now,
            ]);
            $updatedCount++;
        }

        return $updatedCount;
    }

    /**
     * Cari item berdasarkan scan QR / barcode / NUP
     */
    public function lookupScan(int $auditId, string $keyword): ?array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return null;
        }

        // Format umum QR DBR: "KODE|NUP" atau "KODE" atau "NUP"
        $kodeBarang = null;
        $nup = null;

        if (str_contains($keyword, '|')) {
            $parts = explode('|', $keyword);
            $kodeBarang = trim($parts[0] ?? '');
            $nup = trim($parts[1] ?? '');
        }

        $builder = $this->where('audit_id', $auditId);

        if ($kodeBarang && $nup) {
            $builder->groupStart()
                ->where('kode_barang', $kodeBarang)
                ->where('nup', $nup)
                ->groupEnd();
        } else {
            $builder->groupStart()
                ->where('nup', $keyword)
                ->orWhere('kode_barang', $keyword)
                ->orLike('nama_barang', $keyword)
                ->groupEnd();
        }

        return $builder->first();
    }
}
