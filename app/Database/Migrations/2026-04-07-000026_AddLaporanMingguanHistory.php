<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLaporanMingguanHistory extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('laporan_mingguan_histories')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'laporan_mingguan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'sekolah_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'period_start' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'period_end' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'changed_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'changed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('laporan_mingguan_id');
        $this->forge->addKey('changed_at');
        $this->forge->createTable('laporan_mingguan_histories');
    }

    public function down()
    {
        if ($this->db->tableExists('laporan_mingguan_histories')) {
            $this->forge->dropTable('laporan_mingguan_histories', true);
        }
    }
}
