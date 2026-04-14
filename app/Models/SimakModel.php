<?php

namespace App\Models;

use CodeIgniter\Model;

class SimakModel extends Model
{
    protected $table = 'trn_simak';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'satker',
        'ppk',
        'nip',
        'nama_paket',
        'tahun_anggaran',
        'penyedia',
        'nomor_kontrak',
        'tanggal_kontrak',
        'nilai_kontrak',
        'nilai_add_kontrak',
        'status',
        'catatan',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_at',
        'deleted_by',
    ];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    public function getNotDeleted()
    {
        return $this->where('deleted_at', null);
    }

    public function getWithFiles($id)
    {
        $simak = $this->where('id', $id)
            ->where('deleted_at', null)
            ->first();

        if ($simak) {
            $fileModel = new SimakFileModel();
            $simak['files'] = $fileModel->where('simak_id', $id)
                ->orderBy('uploaded_date', 'DESC')
                ->findAll();
        }

        return $simak;
    }
}
