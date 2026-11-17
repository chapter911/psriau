<?php

namespace App\Models;

use CodeIgniter\Model;

class BmnSekolahRakyatDasarPerhitunganModel extends Model
{
    protected $table            = 'trn_bmn_sekolah_rakyat_dasar_perhitungan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'no_urut',
        'nama_aset',
        'komponen_rab',
        'jumlah',
        'satuan',
        'nilai_rab',
        'alokasi_biaya_umum',
        'subtotal',
        'ppn',
        'total_termasuk_ppn',
        'nilai_perolehan_unit',
        'total_nilai_perolehan',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
