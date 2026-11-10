<?php

namespace App\Models;

use CodeIgniter\Model;

class AtkPengadaanItemModel extends Model
{
    protected $table            = 'trn_atk_pengadaan_item';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pengadaan_id',
        'barang_id',
        'nama_barang',
        'satuan',
        'volume',
        'harga_satuan',
        'subtotal',
        'kondisi_barang',
        'keterangan',
        'urutan',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

