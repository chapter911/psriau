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

        $rows = $db->query($sql)->getResultArray();
        foreach ($rows as &$r) {
            $total = (int) ($r['total_item'] ?? 0);
            $berfoto = (int) ($r['item_berfoto'] ?? 0);
            $r['persen'] = $total > 0 ? round(($berfoto / $total) * 100, 1) : 0.0;
        }
        unset($r);

        return $rows;
    }

    /**
     * Alias statistik foto per lokasi
     */
    public function getStatistikFotoPerLokasi(): array
    {
        return $this->getLokasiStats();
    }

    /**
     * Mengambil daftar material/jenis barang yang ada di lokasi gedung tertentu
     */
    public function getMaterialListPerLokasi(string $lokasi): array
    {
        $trimLokasi = trim($lokasi);
        if ($trimLokasi === '') {
            return [];
        }

        return $this->where('lokasi', $trimLokasi)
            ->select('jenis_bmn, COUNT(id) AS total_item')
            ->groupBy('jenis_bmn')
            ->orderBy('jenis_bmn', 'ASC')
            ->findAll();
    }

    /**
     * Mengambil daftar item barang untuk survei foto beserta daftar fotonya
     */
    public function getItemsForFoto(string $lokasi, string $material = '', string $status = ''): array
    {
        $builder = $this->builder()
            ->select('trn_bmn_sekolah_rakyat.*, COUNT(f.id) AS foto_count')
            ->join('trn_bmn_sekolah_rakyat_foto f', 'f.bmn_id = trn_bmn_sekolah_rakyat.id', 'left')
            ->groupBy('trn_bmn_sekolah_rakyat.id');

        if ($lokasi !== '' && $lokasi !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.lokasi', $lokasi);
        }
        if ($material !== '' && $material !== '*') {
            $builder->where('trn_bmn_sekolah_rakyat.jenis_bmn', $material);
        }
        if ($status === 'sudah') {
            $builder->having('foto_count >', 0);
        } elseif ($status === 'belum') {
            $builder->having('foto_count =', 0);
        }

        $items = $builder->orderBy('trn_bmn_sekolah_rakyat.jenis_bmn', 'ASC')
            ->orderBy('trn_bmn_sekolah_rakyat.no_urut', 'ASC')
            ->get()->getResultArray();

        $itemIds = array_column($items, 'id');
        $fotosByItem = [];
        if (! empty($itemIds)) {
            $allFotos = $this->db->table('trn_bmn_sekolah_rakyat_foto')
                ->whereIn('bmn_id', $itemIds)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($allFotos as $f) {
                $fotosByItem[$f['bmn_id']][] = $f;
            }
        }

        foreach ($items as &$item) {
            $item['fotos'] = $fotosByItem[$item['id']] ?? [];
        }
        unset($item);

        return $items;
    }

    /**
     * Rekapitulasi per kelompok BMN
     */
    public function getRekapitulasiKelompok(): array
    {
        return $this->db->table($this->table)
            ->select('kelompok_bmn, COUNT(id) AS total_item, SUM(jumlah) AS total_unit, SUM(total_nilai_perolehan) AS total_nilai')
            ->groupBy('kelompok_bmn')
            ->orderBy('kelompok_bmn', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Rekapitulasi per lokasi
     */
    public function getRekapitulasiLokasi(): array
    {
        return $this->db->table($this->table)
            ->select('lokasi, COUNT(id) AS total_item, SUM(jumlah) AS total_unit, SUM(total_nilai_perolehan) AS total_nilai')
            ->groupBy('lokasi')
            ->orderBy('lokasi', 'ASC')
            ->get()
            ->getResultArray();
    }
}
