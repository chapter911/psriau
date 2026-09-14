<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanDeduplicateInventarisNames extends Migration
{
    /**
     * Standar Kodifikasi Baku BMN Kementerian Keuangan untuk memulihkan nama umum barang
     */
    private const KODIFIKASI_BMN = [
        '3050104001' => 'Lemari Besi/Metal',
        '3050104003' => 'Rak Besi',
        '3050104007' => 'Brandkas',
        '3050105010' => 'White Board',
        '3050105015' => 'Alat Penghancur Kertas',
        '3050105096' => 'Papan Tulis Kaca',
        '3050201001' => 'Meja Kerja Besi/Metal',
        '3050201002' => 'Meja Kerja Kayu',
        '3050201003' => 'Kursi Besi/Metal',
        '3050201008' => 'Meja Rapat',
        '3050201020' => 'Kursi Fiber Glas/Plastik',
        '3050201033' => 'Sofa',
        '3050203005' => 'Air Cleaner',
        '3050204004' => 'A.C. Split',
        '3050204007' => 'Exhause Fan',
        '3050206002' => 'Televisi',
        '3050206007' => 'Loudspeaker',
        '3050206014' => 'Microphone',
        '3050206036' => 'Dispenser',
        '3060101056' => 'Battery Charger (Peralatan Studio Audio)',
        '3060102045' => 'Tripod Camera',
        '3060102128' => 'Camera Digital',
        '3060102132' => 'Video Conference',
        '3060102170' => 'Gimbal Tripod',
        '3060105047' => 'Kamera Udara',
        '3100102001' => 'P.C Unit',
        '3100102003' => 'Note Book',
        '3100102009' => 'Tablet PC',
        '3100203003' => 'Printer (Peralatan Personal Komputer)',
        '3100203004' => 'Scanner (Peralatan Personal Komputer)',
        '3100203017' => 'External/ Portable Hardisk',
        '6070501001' => 'Aset Tetap Lainnya Dalam Renovasi',
        '7010101005' => 'Aset Tetap Lainnya Dalam Pengerjaan',
    ];

    public function up()
    {
        helper('custom');

        $cleanText = static function (?string $str): string {
            if (function_exists('clean_inventaris_text')) {
                return clean_inventaris_text($str);
            }
            if ($str === null) return '';
            $str = preg_replace('/\x{00a0}+/u', ' ', $str);
            $str = trim(preg_replace('/\s+/', ' ', $str));
            $len = strlen($str);
            if ($len >= 6) {
                $half = (int) ($len / 2);
                if ($len % 2 !== 0 && substr($str, 0, $half) === substr($str, $half + 1)) {
                    $str = trim(substr($str, 0, $half));
                } elseif ($len % 2 === 0 && substr($str, 0, $half) === substr($str, $half)) {
                    $str = trim(substr($str, 0, $half));
                }
            }
            if (preg_match('/^(.{3,})\s*[\s\-\/;]+\s*\1$/i', $str, $matches)) {
                $str = trim($matches[1]);
            }
            return $str;
        };

        // 1. Pembersihan Tabel Utama Aset BMN: trn_inventaris_satker
        if ($this->db->tableExists('trn_inventaris_satker')) {
            $items = $this->db->table('trn_inventaris_satker')->get()->getResultArray();
            foreach ($items as $it) {
                $id = (int) $it['id'];
                $kode = trim((string) ($it['kode_barang'] ?? ''));
                $origNama = trim((string) ($it['nama_barang'] ?? ''));
                $origMerkTipe = trim((string) ($it['merk_tipe'] ?? ''));
                $origMerk = trim((string) ($it['merk'] ?? ''));
                $origTipe = trim((string) ($it['tipe'] ?? ''));

                $cleanNama = $cleanText($origNama);
                $cleanMerkTipe = $cleanText($origMerkTipe);
                $cleanMerk = $cleanText($origMerk);
                $cleanTipe = $cleanText($origTipe);

                // Periksa apakah kode barang memiliki kodifikasi resmi BMN
                if (isset(self::KODIFIKASI_BMN[$kode])) {
                    $namaBaku = self::KODIFIKASI_BMN[$kode];
                    // Jika nama_barang sebelumnya bukan nama baku, dan justru menyimpan merk/spesifikasi
                    if (strcasecmp($cleanNama, $namaBaku) !== 0) {
                        // Jika merk_tipe kosong atau sama dengan nama_barang, pindahkan spesifikasi ke merk_tipe
                        if ($cleanMerkTipe === '' || strcasecmp($cleanMerkTipe, $cleanNama) === 0) {
                            $cleanMerkTipe = $cleanNama;
                        }
                        $cleanNama = $namaBaku;
                    }
                }

                // Jika merk dan tipe identik, cegah penggabungan duplikat
                if ($cleanMerkTipe === '' && $cleanMerk !== '' && $cleanTipe !== '') {
                    $cleanMerkTipe = (strcasecmp($cleanMerk, $cleanTipe) === 0) ? $cleanMerk : ($cleanMerk . ' ' . $cleanTipe);
                }

                $updates = [];
                if ($cleanNama !== $origNama) {
                    $updates['nama_barang'] = $cleanNama;
                }
                if ($cleanMerkTipe !== $origMerkTipe) {
                    $updates['merk_tipe'] = $cleanMerkTipe;
                }
                if ($cleanMerk !== $origMerk) {
                    $updates['merk'] = $cleanMerk;
                }
                if ($cleanTipe !== $origTipe) {
                    $updates['tipe'] = $cleanTipe;
                }

                if (! empty($updates)) {
                    $this->db->table('trn_inventaris_satker')->where('id', $id)->update($updates);
                }
            }
        }

        // 2. Pembersihan Tabel trn_inventaris_tidak_terdata
        if ($this->db->tableExists('trn_inventaris_tidak_terdata')) {
            $unregistered = $this->db->table('trn_inventaris_tidak_terdata')->get()->getResultArray();
            foreach ($unregistered as $u) {
                $id = (int) $u['id'];
                $origNama = trim((string) ($u['nama_barang'] ?? ''));
                $origMerk = trim((string) ($u['merk_tipe'] ?? ''));

                $cleanNama = $cleanText($origNama);
                $cleanMerk = $cleanText($origMerk);

                $updates = [];
                if ($cleanNama !== $origNama) {
                    $updates['nama_barang'] = $cleanNama;
                }
                if ($cleanMerk !== $origMerk) {
                    $updates['merk_tipe'] = $cleanMerk;
                }

                if (! empty($updates)) {
                    $this->db->table('trn_inventaris_tidak_terdata')->where('id', $id)->update($updates);
                }
            }
        }
    }

    public function down()
    {
        // Migrasi data cleanup tidak memerlukan rollback karena hanya membersihkan teks duplikat
    }
}
