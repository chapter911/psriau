<?php

namespace App\Models;

use CodeIgniter\Model;

class AtkPengadaanModel extends Model
{
    protected $table            = 'trn_atk_pengadaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tahun_anggaran',
        'bulan',
        'kategori',
        'kegiatan_label',
        'mata_anggaran_id',
        'kode_mata_anggaran',
        'penyedia_id',
        'kop_surat_id',
        'no_usulan',
        'tgl_usulan',
        'no_pesanan',
        'tgl_pesanan',
        'tgl_batas_terima',
        'persen_denda_harian',
        'no_bastp',
        'tgl_bastp',
        'no_faktur',
        'tgl_faktur',
        'no_bukti_kuitansi',
        'tgl_kuitansi',
        'tgl_lunas_bayar',
        'ppk_pegawai_id',
        'ppk_nama',
        'ppk_nip',
        'bendahara_pegawai_id',
        'bendahara_nama',
        'bendahara_nip',
        'total_nilai',
        'terbilang',
        'status',
        'catatan',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'approved_by',
        'approved_at',
        'created_by',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

