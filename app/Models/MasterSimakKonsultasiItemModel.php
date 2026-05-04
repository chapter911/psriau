<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterSimakKonsultasiItemModel extends Model
{
    protected $table = 'mst_simak_konsultasi_item';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'parent_id',
        'row_no',
        'display_no',
        'uraian',
        'bentuk_dokumen',
        'referensi',
        'kriteria_administrasi',
        'kriteria_substansi',
        'sumber_dokumen_hasil_integrasi',
        'row_kind',
        'has_question',
        'ordering',
        'is_active',
        'is_hidden_share',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
