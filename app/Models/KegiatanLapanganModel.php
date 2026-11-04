<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanLapanganModel extends Model
{
    protected $table         = 'kegiatan_lapangan';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title',
        'kategori',
        'activity_date',
        'location',
        'created_by',
        'created_by_user_id',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan daftar kategori default dan kategori kustom yang telah tersimpan.
     *
     * @return string[]
     */
    public function getCategories(): array
    {
        $defaults = [
            'Kegiatan Lapangan',
            'Rapat Internal',
            'Rapat External',
        ];

        try {
            $db = $this->db;
            if (! $db->fieldExists('kategori', $this->table)) {
                return $defaults;
            }

            $rows = $this->select('kategori')
                ->where('kategori IS NOT NULL', null, false)
                ->where('kategori !=', '')
                ->groupBy('kategori')
                ->orderBy('kategori', 'ASC')
                ->findAll();

            $custom = [];
            foreach ($rows as $row) {
                $kat = trim((string) ($row['kategori'] ?? ''));
                if ($kat !== '') {
                    $custom[] = $kat;
                }
            }

            return array_values(array_unique(array_merge($defaults, $custom)));
        } catch (\Throwable $e) {
            return $defaults;
        }
    }
}