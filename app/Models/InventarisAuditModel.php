<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisAuditModel extends Model
{
    protected $table            = 'trn_inventaris_audit';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'kode_audit',
        'judul_audit',
        'lingkup_audit',
        'ruangan_id',
        'ruangan_nama',
        'paket_id',
        'sekolah_npsn',
        'sekolah_nama',
        'tanggal_audit',
        'auditor_nama',
        'auditor_nip',
        'status',
        'catatan',
        'total_item',
        'total_sesuai',
        'total_berubah',
        'total_selisih',
        'total_dipinjam',
        'total_belum',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Generate Kode Audit Unik (format: AUD-YYYYMM-XXXX)
     */
    public function generateKodeAudit(): string
    {
        $prefix = 'AUD-' . date('Ym') . '-';
        $last = $this->like('kode_audit', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        $nextNumber = 1;
        if ($last && ! empty($last['kode_audit'])) {
            $lastSuffix = substr($last['kode_audit'], strlen($prefix));
            if (is_numeric($lastSuffix)) {
                $nextNumber = (int) $lastSuffix + 1;
            }
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Rekalkulasi ringkasan angka metrik audit berdasarkan tabel trn_inventaris_audit_item
     */
    public function recalculateStats(int $auditId): void
    {
        $db = $this->db;
        $items = $db->table('trn_inventaris_audit_item')
            ->select('status_audit, status_pinjam_sistem, COUNT(id) as cnt')
            ->where('audit_id', $auditId)
            ->groupBy(['status_audit', 'status_pinjam_sistem'])
            ->get()
            ->getResultArray();

        $totalItem     = 0;
        $totalSesuai   = 0;
        $totalBerubah  = 0;
        $totalSelisih  = 0;
        $totalDipinjam = 0;
        $totalBelum    = 0;

        foreach ($items as $row) {
            $cnt = (int) $row['cnt'];
            $st  = $row['status_audit'];
            $pj  = $row['status_pinjam_sistem'];

            $totalItem += $cnt;

            if ($pj === 'dipinjam' || $st === 'terkonfirmasi_dipinjam') {
                $totalDipinjam += $cnt;
            }

            switch ($st) {
                case 'sesuai':
                    $totalSesuai += $cnt;
                    break;
                case 'kondisi_berubah':
                case 'salah_lokasi':
                    $totalBerubah += $cnt;
                    break;
                case 'tidak_ditemukan':
                    $totalSelisih += $cnt;
                    break;
                case 'belum_diperiksa':
                    $totalBelum += $cnt;
                    break;
            }
        }

        $this->update($auditId, [
            'total_item'     => $totalItem,
            'total_sesuai'   => $totalSesuai,
            'total_berubah'  => $totalBerubah,
            'total_selisih'  => $totalSelisih,
            'total_dipinjam' => $totalDipinjam,
            'total_belum'    => $totalBelum,
        ]);
    }

    /**
     * Mengambil ringkasan global KPI modul audit
     */
    public function getSummaryKPI(): array
    {
        $totalAudit   = $this->countAllResults(false);
        $totalBerjalan = $this->where('status', 'berjalan')->countAllResults(false);
        $totalSelesai  = $this->where('status', 'selesai')->countAllResults(false);

        $db = $this->db;
        $itemStats = $db->table('trn_inventaris_audit_item')
            ->select('COUNT(id) as total_aset, 
                      SUM(CASE WHEN status_audit = "sesuai" THEN 1 ELSE 0 END) as total_sesuai,
                      SUM(CASE WHEN status_audit = "tidak_ditemukan" THEN 1 ELSE 0 END) as total_selisih')
            ->get()
            ->getRowArray();

        return [
            'total_audit'    => (int) $totalAudit,
            'total_berjalan' => (int) $totalBerjalan,
            'total_selesai'  => (int) $totalSelesai,
            'total_aset'     => (int) ($itemStats['total_aset'] ?? 0),
            'total_sesuai'   => (int) ($itemStats['total_sesuai'] ?? 0),
            'total_selisih'  => (int) ($itemStats['total_selisih'] ?? 0),
        ];
    }
}
