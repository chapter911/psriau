<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarisPengaturanModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // ==========================================
    // KOP SURAT INVENTARIS
    // ==========================================

    public function getKopSuratList(): array
    {
        if (! $this->db->tableExists('cfg_inventaris_kop_surat')) {
            return [];
        }

        return $this->db->table('cfg_inventaris_kop_surat')
            ->orderBy('is_active', 'DESC')
            ->orderBy('berlaku_dari', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getKopSuratByDate(?string $date = null): ?array
    {
        if ($this->db->tableExists('cfg_inventaris_kop_surat')) {
            if (! empty($date)) {
                $matched = $this->db->table('cfg_inventaris_kop_surat')
                    ->where('is_active', 1)
                    ->groupStart()
                        ->where('berlaku_dari <=', $date)
                        ->orWhere('berlaku_dari IS NULL')
                    ->groupEnd()
                    ->groupStart()
                        ->where('berlaku_sampai >=', $date)
                        ->orWhere('berlaku_sampai IS NULL')
                    ->groupEnd()
                    ->orderBy('berlaku_dari', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getRowArray();

                if ($matched) {
                    return $matched;
                }
            }

            // Fallback: Kop aktif terbaru di cfg_inventaris_kop_surat
            $rowCfg = $this->db->table('cfg_inventaris_kop_surat')
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            if ($rowCfg) {
                return $rowCfg;
            }
        }

        // Fallback: master umum kop_surat
        if ($this->db->tableExists('kop_surat')) {
            $rowKs = $this->db->table('kop_surat')
                ->select('id, title AS nama_kop, image_url, is_active')
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            if ($rowKs) {
                return $rowKs;
            }
        }

        return null;
    }

    public function getKopSuratById(int $id): ?array
    {
        if ($this->db->tableExists('cfg_inventaris_kop_surat')) {
            $row = $this->db->table('cfg_inventaris_kop_surat')
                ->where('id', $id)
                ->get()
                ->getRowArray();
            if ($row) {
                return $row;
            }
        }

        if ($this->db->tableExists('kop_surat')) {
            $rowKs = $this->db->table('kop_surat')
                ->select('id, title AS nama_kop, image_url, is_active')
                ->where('id', $id)
                ->get()
                ->getRowArray();
            if ($rowKs) {
                return $rowKs;
            }
        }

        return null;
    }

    // ==========================================
    // RIWAYAT JABATAN KASATKER (KUASA PENGGUNA BARANG)
    // ==========================================

    public function getKasatkerList(): array
    {
        if (! $this->db->tableExists('cfg_inventaris_kasatker')) {
            return [];
        }

        return $this->db->table('cfg_inventaris_kasatker')
            ->orderBy('is_active', 'DESC')
            ->orderBy('periode_mulai', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getKasatkerByDate(?string $date = null): array
    {
        $fallback = [
            'id'             => 0,
            'nama'           => 'Muhammad Yudi Prasetya, ST.',
            'nip'            => '198002142014121002',
            'jabatan'        => 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Penguna Barang',
            'status_jabatan' => 'definitif',
        ];

        if (! $this->db->tableExists('cfg_inventaris_kasatker')) {
            return $fallback;
        }

        if (! empty($date)) {
            $matched = $this->db->table('cfg_inventaris_kasatker')
                ->where('is_active', 1)
                ->groupStart()
                    ->where('periode_mulai <=', $date)
                    ->orWhere('periode_mulai IS NULL')
                ->groupEnd()
                ->groupStart()
                    ->where('periode_selesai >=', $date)
                    ->orWhere('periode_selesai IS NULL')
                ->groupEnd()
                ->orderBy('periode_mulai', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($matched && ! empty($matched['nama'])) {
                return $matched;
            }
        }

        // Fallback: Kasatker aktif terbaru di database
        $active = $this->db->table('cfg_inventaris_kasatker')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        return $active ?: $fallback;
    }

    // ==========================================
    // DELEGASI PENANDATANGAN BMN (a.n. KPB)
    // ==========================================

    public function getDelegasi(): array
    {
        $fallback = [
            'id'                 => 0,
            'pegawai_id'         => null,
            'nama'               => 'Hendrick Bastiar',
            'nip'                => '197810162025211023',
            'jabatan_struktural' => 'Staf Tata Usaha',
            'jabatan_bmn'        => 'Pengurus Barang Pengguna',
            'format_ttd'         => "a.n. Kuasa Pengguna Barang,\nPengurus Barang Pengguna",
            'is_active'          => 1,
        ];

        if (! $this->db->tableExists('cfg_inventaris_delegasi')) {
            return $fallback;
        }

        $row = $this->db->table('cfg_inventaris_delegasi')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        return $row ?: $fallback;
    }

    // ==========================================
    // RESOLUSI CERDAS PIHAK PERTAMA (CEK KONFLIK)
    // ==========================================

    /**
     * Resolusi Pejabat Penyerah BMN (PIHAK PERTAMA).
     * Sesuai ketentuan, penerima dan penyerah diperbolehkan sama (Kasatker menandatangani sebagai kedua pihak jika meminjam BMN).
     */
    public function resolvePihakPertama(string $peminjamNama = '', ?string $peminjamNip = null, ?string $tanggalPinjam = null): array
    {
        $kasatker = $this->getKasatkerByDate($tanggalPinjam);

        return [
            'is_delegasi'       => false,
            'nama'              => $kasatker['nama'] ?? 'Muhammad Yudi Prasetya, ST.',
            'nip'               => $kasatker['nip'] ?? '198002142014121002',
            'jabatan'           => $kasatker['jabatan'] ?? 'Kepala Satuan Kerja Pelaksanaan Prasarana Strategis Riau selaku Kuasa Penguna Barang',
            'jabatan_singkat'   => 'Kepala Satuan Kerja',
            'format_header_ttd' => "yang menyerahkan,\nKepala Satuan Kerja\nPelaksanaan Prasarana Strategis Riau\nSelaku Kuasa Penguna Barang,",
            'kasatker_asli'     => $kasatker,
        ];
    }
}
