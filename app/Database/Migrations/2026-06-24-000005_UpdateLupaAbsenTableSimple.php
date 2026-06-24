<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateLupaAbsenTableSimple extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('lupa_absen')) {
            return;
        }

        // Get existing columns
        $columns = $db->getFieldNames('lupa_absen');

        // Add new columns if they don't exist
        if (! in_array('nama', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN nama VARCHAR(150) NULL AFTER status');
        }

        if (! in_array('nip', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN nip VARCHAR(30) NULL');
        }

        if (! in_array('jabatan_id', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN jabatan_id INT(11) UNSIGNED NULL');
        }

        if (! in_array('jabatan', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN jabatan VARCHAR(255) NULL');
        }

        if (! in_array('unit_kerja', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN unit_kerja VARCHAR(255) NULL');
        }

        if (! in_array('tanggal_absen', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN tanggal_absen DATE NULL');
        }

        if (! in_array('jenis_absen', $columns)) {
            $db->query("ALTER TABLE lupa_absen ADD COLUMN jenis_absen ENUM('Masuk', 'Pulang') NULL");
        }

        if (! in_array('alasan_detail', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN alasan_detail TEXT NULL');
        }

        if (! in_array('nomor_surat', $columns)) {
            $db->query('ALTER TABLE lupa_absen ADD COLUMN nomor_surat VARCHAR(100) NULL');
        }

        // Add indexes
        try {
            $db->query('ALTER TABLE lupa_absen ADD INDEX idx_nip (nip)');
        } catch (\Throwable $e) {}

        try {
            $db->query('ALTER TABLE lupa_absen ADD INDEX idx_tanggal_absen (tanggal_absen)');
        } catch (\Throwable $e) {}

        try {
            $db->query('ALTER TABLE lupa_absen ADD INDEX idx_status (status)');
        } catch (\Throwable $e) {}
    }

    public function down()
    {
        // Not implementing down() to avoid data loss
    }
}
