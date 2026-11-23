<?php

namespace App\Models;

use CodeIgniter\Model;

class BmnMadrasahModel extends Model
{
    protected $table            = 'trn_bmn_madrasah_paket';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'no_paket',
        'nama_paket',
        'wilayah',
        'tahun_anggaran',
        'jenis_bmn',
        'kode_bmn',
        'nilai_kontrak_fisik',
        'nilai_mk_supervisi',
        'total_asp_nilai',
        'total_gabungan',
        'jumlah_madrasah',
        'jumlah_asp_unit',
    ];
    protected $useTimestamps    = true;

    /**
     * Mengambil ringkasan statistik KPI Hasil Penelitian BMN Madrasah PHTC
     */
    public function getSummaryStats(): array
    {
        $db = $this->db;

        $paketRows = $db->table('trn_bmn_madrasah_paket')->get()->getResultArray();

        $totalFisik       = 0.0;
        $totalMk          = 0.0;
        $totalAsp         = 0.0;
        $totalGabungan    = 0.0;
        $totalMadrasah    = 0;
        $totalAspUnit     = 0;

        foreach ($paketRows as $p) {
            $totalFisik    += (float) ($p['nilai_kontrak_fisik'] ?? 0);
            $totalMk       += (float) ($p['nilai_mk_supervisi'] ?? 0);
            $totalAsp      += (float) ($p['total_asp_nilai'] ?? 0);
            $totalGabungan += (float) ($p['total_gabungan'] ?? 0);
            $totalMadrasah += (int) ($p['jumlah_madrasah'] ?? 0);
            $totalAspUnit  += (int) ($p['jumlah_asp_unit'] ?? 0);
        }

        $countNegeri = (int) $db->table('trn_bmn_madrasah_sekolah')->where('status_sekolah', 'Negeri')->countAllResults();
        $countSwasta = (int) $db->table('trn_bmn_madrasah_sekolah')->where('status_sekolah', 'Swasta')->countAllResults();

        return [
            'total_paket'     => count($paketRows),
            'total_fisik'     => $totalFisik,
            'total_mk'        => $totalMk,
            'total_asp'       => $totalAsp,
            'total_gabungan'  => $totalGabungan,
            'total_madrasah'  => $totalMadrasah,
            'count_negeri'    => $countNegeri,
            'count_swasta'    => $countSwasta,
            'total_asp_unit'  => $totalAspUnit,
        ];
    }

    /**
     * Mengambil daftar paket PHTC
     */
    public function getPaketList(?int $paketId = null): array
    {
        $builder = $this->db->table('trn_bmn_madrasah_paket');
        if ($paketId !== null && $paketId > 0) {
            $builder->where('id', $paketId);
        }
        return $builder->orderBy('no_paket', 'ASC')->get()->getResultArray();
    }

    /**
     * Mengambil daftar madrasah sasaran PHTC beserta paket dan acuan DED
     */
    public function getSekolahList(?string $paketId = null, ?string $status = null, ?string $kabupaten = null, ?string $keyword = null): array
    {
        $builder = $this->db->table('trn_bmn_madrasah_sekolah s')
            ->select('s.*, p.no_paket, p.nama_paket, p.wilayah AS wilayah_paket')
            ->join('trn_bmn_madrasah_paket p', 'p.id = s.paket_id', 'left');

        if ($paketId !== null && $paketId !== '' && $paketId !== '*') {
            $builder->where('s.paket_id', (int) $paketId);
        }
        if ($status !== null && $status !== '' && $status !== '*') {
            $builder->where('s.status_sekolah', $status);
        }
        if ($kabupaten !== null && $kabupaten !== '' && $kabupaten !== '*') {
            $builder->where('s.kabupaten', $kabupaten);
        }
        if ($keyword !== null && trim($keyword) !== '') {
            $builder->groupStart()
                ->like('s.nama_sekolah', $keyword)
                ->orLike('s.kabupaten', $keyword)
                ->orLike('s.acuan_ded', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('p.no_paket', 'ASC')
            ->orderBy('s.status_sekolah', 'ASC')
            ->orderBy('s.nama_sekolah', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Mengambil daftar rincian peralatan & mesin (ASP / Meubelair)
     */
    public function getAspList(?string $paketId = null, ?string $namaBarang = null, ?string $keyword = null): array
    {
        $builder = $this->db->table('trn_bmn_madrasah_asp a')
            ->select('a.*, p.no_paket, p.nama_paket, p.wilayah AS wilayah_paket')
            ->join('trn_bmn_madrasah_paket p', 'p.id = a.paket_id', 'left');

        if ($paketId !== null && $paketId !== '' && $paketId !== '*') {
            $builder->where('a.paket_id', (int) $paketId);
        }
        if ($namaBarang !== null && $namaBarang !== '' && $namaBarang !== '*') {
            $builder->where('a.nama_barang', $namaBarang);
        }
        if ($keyword !== null && trim($keyword) !== '') {
            $builder->groupStart()
                ->like('a.nama_barang', $keyword)
                ->orLike('a.merk_tipe', $keyword)
                ->orLike('a.kode_bmn', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('p.no_paket', 'ASC')
            ->orderBy('a.no_urut', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Kabupaten unik madrasah
     */
    public function getDistinctKabupatenSekolah(): array
    {
        $rows = $this->db->table('trn_bmn_madrasah_sekolah')
            ->select('kabupaten')
            ->distinct()
            ->where('kabupaten IS NOT NULL', null, false)
            ->where('kabupaten !=', '')
            ->orderBy('kabupaten', 'ASC')
            ->get()->getResultArray();

        return array_column($rows, 'kabupaten');
    }

    /**
     * Jenis barang ASP unik
     */
    public function getDistinctBarangAsp(): array
    {
        $rows = $this->db->table('trn_bmn_madrasah_asp')
            ->select('nama_barang')
            ->distinct()
            ->orderBy('nama_barang', 'ASC')
            ->get()->getResultArray();

        return array_column($rows, 'nama_barang');
    }
}
