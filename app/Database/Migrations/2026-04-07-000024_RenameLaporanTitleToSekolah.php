<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameLaporanTitleToSekolah extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('laporan_harian_titles') && ! $this->db->tableExists('laporan_sekolah')) {
            $this->forge->renameTable('laporan_harian_titles', 'laporan_sekolah');
        }

        if ($this->db->tableExists('laporan_harian_reports') && $this->db->fieldExists('title_id', 'laporan_harian_reports') && ! $this->db->fieldExists('sekolah_id', 'laporan_harian_reports')) {
            $this->forge->modifyColumn('laporan_harian_reports', [
                'title_id' => [
                    'name' => 'sekolah_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);
        }

        if ($this->db->tableExists('laporan_mingguan_reports') && $this->db->fieldExists('title_id', 'laporan_mingguan_reports') && ! $this->db->fieldExists('sekolah_id', 'laporan_mingguan_reports')) {
            $this->forge->modifyColumn('laporan_mingguan_reports', [
                'title_id' => [
                    'name' => 'sekolah_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('laporan_harian_reports') && $this->db->fieldExists('sekolah_id', 'laporan_harian_reports') && ! $this->db->fieldExists('title_id', 'laporan_harian_reports')) {
            $this->forge->modifyColumn('laporan_harian_reports', [
                'sekolah_id' => [
                    'name' => 'title_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);
        }

        if ($this->db->tableExists('laporan_mingguan_reports') && $this->db->fieldExists('sekolah_id', 'laporan_mingguan_reports') && ! $this->db->fieldExists('title_id', 'laporan_mingguan_reports')) {
            $this->forge->modifyColumn('laporan_mingguan_reports', [
                'sekolah_id' => [
                    'name' => 'title_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);
        }

        if ($this->db->tableExists('laporan_sekolah') && ! $this->db->tableExists('laporan_harian_titles')) {
            $this->forge->renameTable('laporan_sekolah', 'laporan_harian_titles');
        }
    }
}
