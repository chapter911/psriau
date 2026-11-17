<?php

namespace App\Models;

use CodeIgniter\Model;

class BmnSekolahRakyatModel extends Model
{
    protected $table            = 'trn_bmn_sekolah_rakyat';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'kelompok_bmn',
        'no_urut',
        'kode_bmn',
        'nup',
        'jenis_bmn',
        'spesifikasi',
        'jumlah',
        'satuan',
        'nilai_perolehan',
        'total_nilai_perolehan',
        'kondisi',
        'lokasi',
        'keterangan',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil ringkasan statistik KPI BMN Sekolah Rakyat
     */
    public function getSummaryStats(): array
    {
        $db = $this->db;

        $totalItems = (int) $this->countAllResults(false);
        $sumNilaiRow = $this->selectSum('total_nilai_perolehan')->get()->getRowArray();
        $totalNilai = (float) ($sumNilaiRow['total_nilai_perolehan'] ?? 0);

        // Hitung item per kelompok
        $countPeralatan = (int) $db->table($this->table)->where('kelompok_bmn', 'I. Peralatan dan Mesin')->countAllResults();
        $countGedung    = (int) $db->table($this->table)->where('kelompok_bmn', 'II. Gedung dan Bangunan')->countAllResults();
        $countJaringan  = (int) $db->table($this->table)->where('kelompok_bmn', 'III. Jalan, Irigasi dan Jaringan')->countAllResults();

        // Hitung item yang sudah punya foto
        $fotoRows = $db->table('trn_bmn_sekolah_rakyat_foto')
            ->select('bmn_id')
            ->distinct()
            ->get()
            ->getResultArray();
        $bmnIdsWithFoto = array_column($fotoRows, 'bmn_id');
        $totalSudahFoto = count($bmnIdsWithFoto);
        $totalBelumFoto = max(0, $totalItems - $totalSudahFoto);

        $totalFotoFile = (int) $db->table('trn_bmn_sekolah_rakyat_foto')->countAllResults();

        return [
            'total_items'       => $totalItems,
            'total_nilai'       => $totalNilai,
            'count_peralatan'   => $countPeralatan,
            'count_gedung'      => $countGedung,
            'count_jaringan'    => $countJaringan,
            'total_sudah_foto'  => $totalSudahFoto,
            'total_belum_foto'  => $totalBelumFoto,
            'total_foto_file'   => $totalFotoFile,
            'persen_foto'       => $totalItems > 0 ? round(($totalSudahFoto / $totalItems) * 100, 1) : 0,
        ];
    }

    /**
     * Mengambil daftar lokasi (gedung/zona) beserta statistik item dan fotonya
     */
    public function getLokasiStats(): array
    {
        $db = $this->db;
        $sql = "
            SELECT 
                b.lokasi,
                COUNT(b.id) AS total_item,
                SUM(b.total_nilai_perolehan) AS total_nilai,
                COUNT(DISTINCT f.bmn_id) AS item_berfoto,
                COUNT(f.id) AS total_foto
            FROM trn_bmn_sekolah_rakyat b
            LEFT JOIN trn_bmn_sekolah_rakyat_foto f ON f.bmn_id = b.id
            GROUP BY b.lokasi
            ORDER BY b.lokasi ASC
        ";

        return $db->query($sql)->getResultArray();
    }
}
