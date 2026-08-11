<?php

namespace App\Models;

use CodeIgniter\Model;

class MstTanggalMerahModel extends Model
{
    protected $table            = 'mst_tanggal_merah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tanggal',
        'tahun',
        'nama_libur',
        'tipe',
        'hari',
        'sumber',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil seluruh tanggal merah untuk tahun tertentu diurutkan berdasarkan tanggal
     */
    public function getByYear(int $year): array
    {
        return $this->where('tahun', $year)
            ->orderBy('tanggal', 'ASC')
            ->findAll();
    }

    /**
     * Hitung statistik libur per tahun
     */
    public function getStatsByYear(int $year): array
    {
        $all = $this->getByYear($year);
        $total = count($all);
        $holidays = 0;
        $leaves = 0;

        foreach ($all as $item) {
            if (($item['tipe'] ?? 'holiday') === 'leave') {
                $leaves++;
            } else {
                $holidays++;
            }
        }

        return [
            'total'          => $total,
            'total_holidays' => $holidays,
            'total_leave'    => $leaves,
            'year'           => $year,
        ];
    }

    /**
     * Ambil data tanggal merah sebagai associative array keyed by 'YYYY-MM-DD' untuk kalender
     */
    public function getCalendarMapByYear(int $year): array
    {
        $rows = $this->getByYear($year);
        $map = [];

        foreach ($rows as $r) {
            $date = (string) ($r['tanggal'] ?? '');
            if ($date !== '') {
                $map[$date] = $r;
            }
        }

        return $map;
    }
}
