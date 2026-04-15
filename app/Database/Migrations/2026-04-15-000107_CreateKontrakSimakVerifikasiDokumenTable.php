<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKontrakSimakVerifikasiDokumenTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_kontrak_simak_verifikasi_dokumen')) {
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
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_original_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_stored_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_relative_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'file_mime' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'file_size' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'default' => 0,
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
        $this->forge->addKey(['simak_id', 'row_no']);
        $this->forge->addForeignKey('simak_id', 'trn_kontrak_simak', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('trn_kontrak_simak_verifikasi_dokumen', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_kontrak_simak_verifikasi_dokumen', true);
    }
}
