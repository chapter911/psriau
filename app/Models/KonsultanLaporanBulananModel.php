<?php

namespace App\Models;

use CodeIgniter\Model;

class KonsultanLaporanBulananModel extends Model
{
    protected $table            = 'trn_konsultan_laporan_bulanan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'pegawai_id',
        'periode_bulan',
        'tahun',
        'bulan',
        'file_pdf',
        'file_size',
        'file_original_name',
        'keterangan',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Get monthly reports with joined employee data
     */
    public function getWithPegawai(?int $pegawaiId = null, ?int $tahun = null, ?int $bulan = null, ?string $search = null)
    {
        $builder = $this->builder();
        $builder->select('trn_konsultan_laporan_bulanan.*, p.nama AS nama_pegawai, p.nip AS nip_pegawai, p.foto, ju.jabatan AS jabatan_label');
        $builder->join('mst_pegawai p', 'p.id = trn_konsultan_laporan_bulanan.pegawai_id', 'left');
        $builder->join('mst_jabatan ju', 'ju.id = p.jabatan_utama_id', 'left');

        if ($pegawaiId !== null && $pegawaiId > 0) {
            $builder->where('trn_konsultan_laporan_bulanan.pegawai_id', $pegawaiId);
        }

        if ($tahun !== null && $tahun > 0) {
            $builder->where('trn_konsultan_laporan_bulanan.tahun', $tahun);
        }

        if ($bulan !== null && $bulan > 0) {
            $builder->where('trn_konsultan_laporan_bulanan.bulan', $bulan);
        }

        if (! empty($search)) {
            $builder->groupStart();
            $builder->like('p.nama', $search);
            $builder->orLike('p.nip', $search);
            $builder->orLike('trn_konsultan_laporan_bulanan.periode_bulan', $search);
            $builder->orLike('trn_konsultan_laporan_bulanan.keterangan', $search);
            $builder->groupEnd();
        }

        $builder->orderBy('trn_konsultan_laporan_bulanan.periode_bulan', 'DESC');
        $builder->orderBy('trn_konsultan_laporan_bulanan.id', 'DESC');

        return $builder->get()->getResultArray();
    }
}
