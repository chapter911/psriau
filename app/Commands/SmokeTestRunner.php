<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SmokeTestRunner extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'simak:smoke-test';
    protected $description = 'Menjalankan smoke test data Konstruksi dan Konsultasi.';

    public function run(array $params)
    {
        $db = db_connect();

        CLI::write("=== MENJALANKAN SMOKE TEST DATA SIMAK ===", "blue");

        // 1. Ambil list template leaf rows untuk Konstruksi
        $kontrakController = new \App\Controllers\Admin\Kontrak();
        $reflection = new \ReflectionClass($kontrakController);
        $getSimakTemplateItems = $reflection->getMethod('getSimakTemplateItems');
        $getSimakTemplateItems->setAccessible(true);

        $templateFisik = $getSimakTemplateItems->invoke($kontrakController, 'konstruksi', true);
        $flatFisikLeafs = [];
        foreach ($templateFisik as $item) {
            if (($item['is_leaf'] ?? false) === true && (int) ($item['row_no'] ?? 0) > 0) {
                $flatFisikLeafs[] = $item;
            }
        }
        $totalFisikLeafs = count($flatFisikLeafs);
        CLI::write("Konstruksi: total leaf rows = {$totalFisikLeafs}", "yellow");

        // 2. Ambil list template leaf rows untuk Konsultasi
        $templateKonsultasi = $getSimakTemplateItems->invoke($kontrakController, 'konsultasi', true);
        $flatKonsultasiLeafs = [];
        foreach ($templateKonsultasi as $item) {
            if (($item['is_leaf'] ?? false) === true && (int) ($item['row_no'] ?? 0) > 0) {
                $flatKonsultasiLeafs[] = $item;
            }
        }
        $totalKonsultasiLeafs = count($flatKonsultasiLeafs);
        CLI::write("Konsultasi: total leaf rows = {$totalKonsultasiLeafs}", "yellow");

        if ($totalFisikLeafs === 0 || $totalKonsultasiLeafs === 0) {
            CLI::write("Error: Template data kosong atau file contoh_simak.xlsx tidak ditemukan/tidak terbaca.", "red");
            return;
        }

        // Tentukan jumlah baris
        // lengkap > 20%
        $fisikLengkap = (int) ceil($totalFisikLeafs * 0.22);
        // Belum Sesuai > 5%
        $fisikBelumSesuai = (int) ceil($totalFisikLeafs * 0.07);

        $konsultasiLengkap = (int) ceil($totalKonsultasiLeafs * 0.22);
        $konsultasiBelumSesuai = (int) ceil($totalKonsultasiLeafs * 0.07);

        CLI::write("Konstruksi target: Lengkap = {$fisikLengkap} (" . round(($fisikLengkap / $totalFisikLeafs)*100, 2) . "%), Belum Sesuai = {$fisikBelumSesuai} (" . round(($fisikBelumSesuai / $totalFisikLeafs)*100, 2) . "%)", "green");
        CLI::write("Konsultasi target: Lengkap = {$konsultasiLengkap} (" . round(($konsultasiLengkap / $totalKonsultasiLeafs)*100, 2) . "%), Belum Sesuai = {$konsultasiBelumSesuai} (" . round(($konsultasiBelumSesuai / $totalKonsultasiLeafs)*100, 2) . "%)", "green");

        $db->transStart();

        // 3. Insert data Konstruksi
        $fisikNomor = 'SMOKE-TEST-KONTRAK-FISIK';
        $db->table('trn_kontrak_simak')->where('nomor_kontrak', $fisikNomor)->delete();
        $db->table('trn_kontrak_simak')->insert([
            'satker' => 'Perencanaan Prasarana Strategis',
            'ppk_nama' => 'Smoke Test PPK',
            'ppk_nip' => '1234567890',
            'nama_paket' => 'SMOKE TEST PAKET FISIK',
            'tahun_anggaran' => '2026',
            'penyedia' => 'Smoke Test Penyedia',
            'nomor_kontrak' => $fisikNomor,
            'nilai_kontrak' => 500000000,
            'created_by' => 'system_smoke_test',
            'created_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $fisikId = $db->insertID();
        CLI::write("Berhasil membuat kontrak Konstruksi ID: {$fisikId}", "green");

        // Isi verifikasi Konstruksi
        $index = 0;
        foreach ($flatFisikLeafs as $leaf) {
            $rowNo = (int) $leaf['row_no'];
            $kode = (string) ($leaf['display_no'] ?? '');
            $uraian = (string) ($leaf['uraian'] ?? '');

            if ($index < $fisikLengkap) {
                // Lengkap
                $db->table('trn_kontrak_simak_verifikasi')->insert([
                    'simak_id' => $fisikId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'sesuai',
                    'keterangan' => 'Smoke Test Lengkap',
                    'pic' => 'Smoke Test',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert([
                    'simak_id' => $fisikId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'sesuai',
                    'keterangan' => 'Smoke Test Lengkap',
                    'pic' => 'Smoke Test',
                    'file_original_name' => 'smoke-test.pdf',
                    'file_stored_name' => '',
                    'file_relative_path' => 'https://drive.google.com/file/d/1smoke-test-fisik-lengkap/view',
                    'file_mime' => 'application/pdf',
                    'file_size' => 12345,
                    'tipe_dokumen' => 'final',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } elseif ($index < ($fisikLengkap + $fisikBelumSesuai)) {
                // Belum Sesuai
                $db->table('trn_kontrak_simak_verifikasi')->insert([
                    'simak_id' => $fisikId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'belum_sesuai',
                    'keterangan' => 'Smoke Test Belum Sesuai',
                    'pic' => 'Smoke Test',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $db->table('trn_kontrak_simak_verifikasi_dokumen')->insert([
                    'simak_id' => $fisikId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'tidak_sesuai',
                    'keterangan' => 'Smoke Test Belum Sesuai',
                    'pic' => 'Smoke Test',
                    'file_original_name' => 'smoke-test.pdf',
                    'file_stored_name' => '',
                    'file_relative_path' => 'https://drive.google.com/file/d/1smoke-test-fisik-belumesuai/view',
                    'file_mime' => 'application/pdf',
                    'file_size' => 12345,
                    'tipe_dokumen' => 'final',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $index++;
        }

        // 4. Insert data Konsultasi
        $konsultasiNomor = 'SMOKE-TEST-KONTRAK-KONSULTASI';
        $db->table('trn_kontrak_simak_konsultasi')->where('nomor_kontrak', $konsultasiNomor)->delete();
        $db->table('trn_kontrak_simak_konsultasi')->insert([
            'satker' => 'Perencanaan Prasarana Strategis',
            'ppk_nama' => 'Smoke Test PPK',
            'ppk_nip' => '1234567890',
            'nama_paket' => 'SMOKE TEST PAKET KONSULTASI',
            'tahun_anggaran' => '2026',
            'penyedia' => 'Smoke Test Penyedia',
            'nomor_kontrak' => $konsultasiNomor,
            'nilai_kontrak' => 500000000,
            'created_by' => 'system_smoke_test',
            'created_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $konsultasiId = $db->insertID();
        CLI::write("Berhasil membuat kontrak Konsultasi ID: {$konsultasiId}", "green");

        // Isi verifikasi Konsultasi
        $index = 0;
        foreach ($flatKonsultasiLeafs as $leaf) {
            $rowNo = (int) $leaf['row_no'];
            $kode = (string) ($leaf['display_no'] ?? '');
            $uraian = (string) ($leaf['uraian'] ?? '');

            if ($index < $konsultasiLengkap) {
                // Lengkap
                $db->table('trn_kontrak_simak_konsultasi_verifikasi')->insert([
                    'simak_id' => $konsultasiId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'sesuai',
                    'keterangan' => 'Smoke Test Lengkap',
                    'pic' => 'Smoke Test',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')->insert([
                    'simak_id' => $konsultasiId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'sesuai',
                    'keterangan' => 'Smoke Test Lengkap',
                    'pic' => 'Smoke Test',
                    'file_original_name' => 'smoke-test.pdf',
                    'file_stored_name' => '',
                    'file_relative_path' => 'https://drive.google.com/file/d/1smoke-test-konsul-lengkap/view',
                    'file_mime' => 'application/pdf',
                    'file_size' => 12345,
                    'nomor_dokumen' => '123',
                    'tipe_dokumen' => 'final',
                    'nama_file' => 'smoke-test.pdf',
                    'file_path' => 'https://drive.google.com/file/d/1smoke-test-konsul-lengkap/view',
                    'status' => 'Lengkap',
                    'catatan' => 'Smoke Test Lengkap',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } elseif ($index < ($konsultasiLengkap + $konsultasiBelumSesuai)) {
                // Belum Sesuai
                $db->table('trn_kontrak_simak_konsultasi_verifikasi')->insert([
                    'simak_id' => $konsultasiId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'belum_sesuai',
                    'keterangan' => 'Smoke Test Belum Sesuai',
                    'pic' => 'Smoke Test',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $db->table('trn_kontrak_simak_konsultasi_verifikasi_dokumen')->insert([
                    'simak_id' => $konsultasiId,
                    'row_no' => $rowNo,
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'kelengkapan_dokumen' => 'ada',
                    'verifikasi_ki' => 'tidak_sesuai',
                    'keterangan' => 'Smoke Test Belum Sesuai',
                    'pic' => 'Smoke Test',
                    'file_original_name' => 'smoke-test.pdf',
                    'file_stored_name' => '',
                    'file_relative_path' => 'https://drive.google.com/file/d/1smoke-test-konsul-belumesuai/view',
                    'file_mime' => 'application/pdf',
                    'file_size' => 12345,
                    'nomor_dokumen' => '123',
                    'tipe_dokumen' => 'final',
                    'nama_file' => 'smoke-test.pdf',
                    'file_path' => 'https://drive.google.com/file/d/1smoke-test-konsul-belumesuai/view',
                    'status' => 'Belum Sesuai',
                    'catatan' => 'Smoke Test Belum Sesuai',
                    'created_by' => 'system_smoke_test',
                    'created_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $index++;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            CLI::write("Error: Gagal menyimpan data transaksi ke database.", "red");
            return;
        }

        CLI::write("=== VERIFIKASI PERSENTASE ===", "blue");

        // Panggil helper getSimakAdministrasiKelengkapanBySimakId
        $getSimakAdministrasiKelengkapanBySimakId = $reflection->getMethod('getSimakAdministrasiKelengkapanBySimakId');
        $getSimakAdministrasiKelengkapanBySimakId->setAccessible(true);

        // Fisik
        $resFisik = $getSimakAdministrasiKelengkapanBySimakId->invoke($kontrakController, [$fisikId], 'konstruksi', false);
        $percentFisik = $resFisik[$fisikId];
        CLI::write("Hasil Kalkulasi Konstruksi:", "yellow");
        CLI::write(" - Lengkap: " . $percentFisik['lengkap_persen'] . "% (Target > 20%)", $percentFisik['lengkap_persen'] > 20 ? "green" : "red");
        CLI::write(" - Belum Sesuai: " . $percentFisik['belum_sesuai_persen'] . "% (Target > 5%)", $percentFisik['belum_sesuai_persen'] > 5 ? "green" : "red");

        // Konsultasi
        $resKonsul = $getSimakAdministrasiKelengkapanBySimakId->invoke($kontrakController, [$konsultasiId], 'konsultasi', false);
        $percentKonsul = $resKonsul[$konsultasiId];
        CLI::write("Hasil Kalkulasi Konsultasi:", "yellow");
        CLI::write(" - Lengkap: " . $percentKonsul['lengkap_persen'] . "% (Target > 20%)", $percentKonsul['lengkap_persen'] > 20 ? "green" : "red");
        CLI::write(" - Belum Sesuai: " . $percentKonsul['belum_sesuai_persen'] . "% (Target > 5%)", $percentKonsul['belum_sesuai_persen'] > 5 ? "green" : "red");

        CLI::write("=== SMOKE TEST SELESAI DENGAN SUKSES ===", "green");
        CLI::write("ID Kontrak Fisik Baru: {$fisikId}", "blue");
        CLI::write("ID Kontrak Konsultasi Baru: {$konsultasiId}", "blue");

        CLI::write("\n=== QUERY SQL UNTUK MENGHAPUS DATA SMOKE TEST ===", "yellow");
        $sql = "
DELETE FROM trn_kontrak_simak_verifikasi_dokumen WHERE simak_id = {$fisikId};
DELETE FROM trn_kontrak_simak_verifikasi WHERE simak_id = {$fisikId};
DELETE FROM trn_kontrak_simak WHERE id = {$fisikId};

DELETE FROM trn_kontrak_simak_konsultasi_verifikasi_dokumen WHERE simak_id = {$konsultasiId};
DELETE FROM trn_kontrak_simak_konsultasi_verifikasi WHERE simak_id = {$konsultasiId};
DELETE FROM trn_kontrak_simak_konsultasi WHERE id = {$konsultasiId};
        ";
        CLI::write($sql, "light_cyan");
    }
}
