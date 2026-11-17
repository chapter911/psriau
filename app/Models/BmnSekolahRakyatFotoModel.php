<?php

namespace App\Models;

use CodeIgniter\Model;

class BmnSekolahRakyatFotoModel extends Model
{
    protected $table            = 'trn_bmn_sekolah_rakyat_foto';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'bmn_id',
        'judul_foto',
        'foto_asli',
        'foto_watermark',
        'latitude',
        'longitude',
        'taken_at',
        'keterangan',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getFotoByBmnId(int $bmnId): array
    {
        return $this->where('bmn_id', $bmnId)->orderBy('id', 'ASC')->findAll();
    }
}
