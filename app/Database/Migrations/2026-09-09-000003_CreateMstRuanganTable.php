<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMstRuanganTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('mst_ruangan')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_ruangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_ruangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'pegawai_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'penanggung_jawab_nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'penanggung_jawab_nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'lokasi_lantai' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
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
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_ruangan');
        $this->forge->addKey('nama_ruangan');
        $this->forge->createTable('mst_ruangan', true);
    }

    public function down()
    {
        $this->forge->dropTable('mst_ruangan', true);
    }
}
