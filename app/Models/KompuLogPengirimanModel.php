<?php

namespace App\Models;

use CodeIgniter\Model;

class KompuLogPengirimanModel extends Model
{
    protected $table            = 'trn_kompu_log_pengiriman';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'sosmed_id',
        'nama_sosmed',
        'user_id',
        'user_nip',
        'user_name',
        'email_tujuan',
        'status',
        'pesan_status',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    protected $useTimestamps    = false;
}
