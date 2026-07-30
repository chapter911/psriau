<?php

namespace App\Models;

use CodeIgniter\Model;

class SuratCutiModel extends Model
{
    protected $table            = 'surat_cuti';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nomor_surat',
        'tanggal_pengajuan',
        'pegawai_id',
        'nama',
        'nip',
        'jabatan',
        'masa_kerja',
        'unit_kerja',
        'jenis_cuti',
        'alasan_cuti',
        'lama_cuti_jumlah',
        'lama_cuti_satuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'alamat_selama_cuti',
        'telepon',
        'catatan_cuti_n2',
        'catatan_cuti_n1',
        'catatan_cuti_n',
        'catatan_cuti_keterangan',
        'atasan_nama',
        'atasan_nip',
        'atasan_jabatan',
        'pejabat_nama',
        'pejabat_nip',
        'pejabat_jabatan',
        'pertimbangan_atasan',
        'keputusan_pejabat',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
