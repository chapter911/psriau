<?php

namespace App\Models;

use CodeIgniter\Model;

class AtkBarangModel extends Model
{
    protected $table            = 'mst_atk_barang';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'penyedia_id',
        'tahun_anggaran',
        'kategori',
        'nama_barang',
        'satuan',
        'harga_satuan',
        'termasuk_pajak',
        'keterangan',
        'is_active',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

