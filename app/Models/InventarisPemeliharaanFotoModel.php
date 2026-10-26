<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisPemeliharaanFotoModel extends Model
{
    protected $table            = 'trn_inventaris_pemeliharaan_foto';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'pemeliharaan_id',
        'kelompok_key',
        'nama_barang',
        'nup_gabungan',
        'foto_depan',
        'foto_atas',
        'foto_bawah',
        'foto_belakang',
        'urutan',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
