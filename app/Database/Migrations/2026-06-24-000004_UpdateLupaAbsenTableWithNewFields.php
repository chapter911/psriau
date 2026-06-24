<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateLupaAbsenTableWithNewFields extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('lupa_absen')) {
            return;
        }

        // Add new columns if they don't exist
        $columns = $db->getFieldNames('lupa_absen');

        if (! in_array('nama', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN nama VARCHAR(150) NULL AFTER nip');
        }

        if (! in_array('jabatan_id', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN jabatan_id INT(11) UNSIGNED NULL AFTER nip');
        }

        if (! in_array('jabatan', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN jabatan VARCHAR(255) NULL AFTER jabatan_id');
        }

        if (! in_array('unit_kerja', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN unit_kerja VARCHAR(255) NULL');
        }

        if (! in_array('tanggal_surat', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN tanggal_surat DATE NULL');
        }

        if (! in_array('nomor_surat', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN nomor_surat VARCHAR(100) NULL');
        }

        if (! in_array('alasan_kategori', $columns)) {
            $db->query("ALTER TABLE lupa_absen ADD COLUMN alasan_kategori VARCHAR(100) NULL");
        }

        if (! in_array('alasan_detail', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN alasan_detail TEXT NULL');
        }

        if (! in_array('entries_json', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN entries_json LONGTEXT NULL');
        }

        // Rename old columns to new format if they exist
        if (in_array('tanggal_absen', $columns) && in_array('entries_json', $columns)) {
            // Check if there's data in old format and migrate
            $rows = $db->table('lupa_absen')
                ->where('entries_json IS NULL', null, false)
                ->orWhere('entries_json', '')
                ->where('tanggal_absen IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                if (! empty($row['tanggal_absen'])) {
                    $entry = [
                        'tanggal' => $row['tanggal_absen'],
                        'hari' => !empty($row['hari']) ? $row['hari'] : '',
                        'jam' => $row['jam_absen'] ?? '',
                        'jenis' => ($row['jenis_absen'] ?? '') === 'masuk' ? 'Masuk' : 'Pulang',
                        'keterangan' => $row['keterangan'] ?? '',
                    ];
                    $db->table('lupa_absen')
                        ->where('id', $row['id'])
                        ->update(['entries_json' => json_encode([$entry], JSON_UNESCAPED_UNICODE)]);
                }
            }
        }

        // Add indexes
        if (! $db->query("SHOW INDEX FROM lupa_absen WHERE Key_name = 'idx_nama'")->getRow()) {
            $db->query('ALTER TABLE lupa_absen ADD INDEX idx_nama (nama)');
        }

        if (! $db->query("SHOW INDEX FROM lupa_absen WHERE Key_name = 'idx_tanggal_surat'")->getRow()) {
            $db->query('ALTER TABLE lupa_absen ADD INDEX idx_tanggal_surat (tanggal_surat)');
        }
    }

    public function down()
    {
        // This migration adds columns, down() would require careful data handling
        // Not implementing down() to avoid data loss
    }
}
