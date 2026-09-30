<?php

namespace App\Models;

use CodeIgniter\Model;

class SurketKinerjaTaModel extends Model
{
    protected $table            = 'trn_surket_kinerja_ta';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nomor_surat',
        'tanggal_surat',
        'kota_surat',
        'ppk_pegawai_id',
        'ppk_nama',
        'ppk_nip',
        'ppk_jabatan',
        'ppk_satker',
        'ppk_alamat',
        'nama_tenaga_ahli',
        'jabatan_pekerjaan',
        'nama_badan_usaha',
        'alamat_badan_usaha',
        'paket_id',
        'nama_paket',
        'lingkup_jasa',
        'lokasi_pekerjaan',
        'nomor_tanggal_kontrak',
        'nilai_kontrak',
        'sumber_dana',
        'masa_penugasan',
        'status_pekerjaan',
        'penilaian_keseluruhan',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Get records with optional search and joins
     */
    public function getList(?string $search = null)
    {
        $builder = $this->builder();
        $builder->select('trn_surket_kinerja_ta.*, mp.nama_paket AS master_nama_paket');
        $builder->join('mst_paket mp', 'mp.id = trn_surket_kinerja_ta.paket_id', 'left');

        if (! empty($search)) {
            $builder->groupStart();
            $builder->like('trn_surket_kinerja_ta.nama_tenaga_ahli', $search);
            $builder->orLike('trn_surket_kinerja_ta.jabatan_pekerjaan', $search);
            $builder->orLike('trn_surket_kinerja_ta.nama_badan_usaha', $search);
            $builder->orLike('trn_surket_kinerja_ta.nama_paket', $search);
            $builder->orLike('trn_surket_kinerja_ta.nomor_surat', $search);
            $builder->orLike('trn_surket_kinerja_ta.nomor_tanggal_kontrak', $search);
            $builder->groupEnd();
        }

        $builder->orderBy('trn_surket_kinerja_ta.id', 'DESC');

        return $builder->get()->getResultArray();
    }
}
