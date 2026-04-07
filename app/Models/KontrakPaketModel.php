<?php

namespace App\Models;

use CodeIgniter\Model;

class KontrakPaketModel extends Model
{
    protected $table = 'trn_kontrak_paket';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'nama_paket',
        'laporan',
        'hasil',
        'tugas_tanggung_jawab',
        'created_by',
        'created_date',
        'created_at',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];
}
