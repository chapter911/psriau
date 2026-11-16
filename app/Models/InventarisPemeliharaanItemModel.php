<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisPemeliharaanItemModel extends Model
{
    protected $table            = 'trn_inventaris_pemeliharaan_item';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'pemeliharaan_id',
        'inventaris_id',
        'jenis_barang',
        'kode_barang',
        'nama_barang',
        'merk_tipe',
        'nup',
        'kondisi',
        'tahun_perolehan',
        'lokasi_ruangan',
        'kelompok_key',
        'urutan',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
