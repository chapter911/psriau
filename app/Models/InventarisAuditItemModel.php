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
     * Mengambil item audit dengan filter status, pencarian, dan sorting
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
