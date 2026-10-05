<?php

namespace App\Models;

use CodeIgniter\Model;

class BastModel extends Model
{
    protected $table            = 'trn_bast';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nomor_bast',
        'judul_bast',
        'jenis_pekerjaan',
        'lingkup_jasa',
        'paket_id',
        'nama_paket',
        'tanggal_bast',
        'kota_bast',
        'kop_surat_id',
        'ppk_pegawai_id',
        'ppk_nama',
        'ppk_nip',
        'ppk_jabatan',
        'ppk_satker',
        'ppk_alamat',
        'penyedia_nama',
        'penyedia_wakil',
        'penyedia_jabatan',
        'penyedia_alamat',
        'dasar_pelaksanaan',
        'rincian_hasil_pekerjaan',
        'kesesuaian_pekerjaan',
        'persentase_pembayaran',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Get records with optional search, paket, lingkup jasa, and joins
     */
    public function getList(?string $search = null, ?int $paketId = null, ?string $lingkupJasa = null)
    {
        $builder = $this->builder();
        $builder->select('trn_bast.*, mp.nama_paket AS master_nama_paket, ks.title AS kop_surat_title');
        $builder->join('mst_paket mp', 'mp.id = trn_bast.paket_id', 'left');
        $builder->join('kop_surat ks', 'ks.id = trn_bast.kop_surat_id', 'left');

        if (! empty($search)) {
            $builder->groupStart();
            $builder->like('trn_bast.nomor_bast', $search);
            $builder->orLike('trn_bast.judul_bast', $search);
            $builder->orLike('trn_bast.nama_paket', $search);
            $builder->orLike('trn_bast.penyedia_nama', $search);
            $builder->orLike('trn_bast.penyedia_wakil', $search);
            $builder->orLike('trn_bast.ppk_nama', $search);
            $builder->groupEnd();
        }

        if (! empty($paketId)) {
            $builder->where('trn_bast.paket_id', (int) $paketId);
        }

        if (! empty($lingkupJasa)) {
            $builder->where('trn_bast.lingkup_jasa', trim($lingkupJasa));
        }

        // Urutkan berdasarkan: 1. Tanggal BAST DESC, 2. ID DESC
        $builder->orderBy('trn_bast.tanggal_bast', 'DESC');
        $builder->orderBy('trn_bast.id', 'DESC');

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['dasar_pelaksanaan_array'] = ! empty($row['dasar_pelaksanaan']) ? json_decode($row['dasar_pelaksanaan'], true) : [];
            $row['rincian_hasil_array']     = ! empty($row['rincian_hasil_pekerjaan']) ? json_decode($row['rincian_hasil_pekerjaan'], true) : [];
        }

        return $rows;
    }
}
