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
        if (! $this->db->tableExists('cfg_inventaris_kop_surat')) {
            return null;
        }

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

        // Fallback: Kop aktif terbaru
        return $this->db->table('cfg_inventaris_kop_surat')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
    }

    public function getKopSuratById(int $id): ?array
    {
        if (! $this->db->tableExists('cfg_inventaris_kop_surat')) {
            return null;
        }

        return $this->db->table('cfg_inventaris_kop_surat')
            ->where('id', $id)
            ->get()
            ->getRowArray();
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
     * Memeriksa apakah peminjam sama dengan Kasatker.
     * Jika sama, PIHAK PERTAMA dialihkan secara hukum ke Pengurus Barang Pengguna (a.n. KPB).
     */
    public function resolvePihakPertama(string $peminjamNama, ?string $peminjamNip = null, ?string $tanggalPinjam = null): array
    {
        $kasatker = $this->getKasatkerByDate($tanggalPinjam);
        $delegasi = $this->getDelegasi();

        $isConflict = false;

        // 1. Cek kesamaan NIP
        $nipKasatkerClean = preg_replace('/[^0-9]/', '', (string) ($kasatker['nip'] ?? ''));
        $nipPeminjamClean = preg_replace('/[^0-9]/', '', (string) $peminjamNip);

        if (! empty($nipKasatkerClean) && ! empty($nipPeminjamClean) && $nipKasatkerClean === $nipPeminjamClean) {
            $isConflict = true;
        }

        // 2. Cek kesamaan Nama jika NIP tidak cocok/kosong
        if (! $isConflict && ! empty($peminjamNama)) {
            $cleanKasatker = strtolower(preg_replace('/[^a-zA-Z]/', '', $kasatker['nama'] ?? ''));
            $cleanPeminjam = strtolower(preg_replace('/[^a-zA-Z]/', '', $peminjamNama));

            // Jika kata kunci nama inti cocok (misal: "muhammadyudiprasetya")
            if (str_contains($cleanPeminjam, 'yudiprasetya') || str_contains($cleanPeminjam, 'muhammadyudi')) {
                $isConflict = true;
            } elseif ($cleanKasatker !== '' && $cleanPeminjam !== '' && (str_contains($cleanKasatker, $cleanPeminjam) || str_contains($cleanPeminjam, $cleanKasatker))) {
                $isConflict = true;
            }
        }

        if ($isConflict) {
            return [
                'is_delegasi'       => true,
                'nama'              => $delegasi['nama'] ?? 'Hendrick Bastiar',
                'nip'               => $delegasi['nip'] ?? '197810162025211023',
                'jabatan'           => ($delegasi['jabatan_bmn'] ?? 'Pengurus Barang Pengguna') . ' selaku Kuasa Pengguna Barang (a.n. Kuasa Pengguna Barang)',
                'jabatan_singkat'   => $delegasi['jabatan_bmn'] ?? 'Pengurus Barang Pengguna',
                'format_header_ttd' => "a.n. Kuasa Pengguna Barang,\n" . ($delegasi['jabatan_bmn'] ?? 'Pengurus Barang Pengguna') . ',',
                'kasatker_asli'     => $kasatker,
            ];
        }

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
