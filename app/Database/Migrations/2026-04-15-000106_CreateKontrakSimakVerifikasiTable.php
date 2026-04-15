<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakVerifikasiTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_verifikasi')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'simak_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'row_no' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'kode' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'uraian' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kelengkapan_dokumen' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'verifikasi_ki' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pic' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'updated_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_by' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('simak_id');
        $this->forge->addKey('row_no');
        $this->forge->addUniqueKey(['simak_id', 'row_no']);
        $this->forge->addForeignKey('simak_id', 'trn_kontrak_simak', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('trn_kontrak_simak_verifikasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak_verifikasi', true);
    }
}
