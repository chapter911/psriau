<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSyaratUmumTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'paket_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'jabatan_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'laporan' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'hasil' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'tugas_tanggung_jawab' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['paket_id', 'jabatan_name']);
        $this->forge->createTable('trn_syarat_umum_kontrak_ki', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_syarat_umum_kontrak_ki', true);
    }
}
