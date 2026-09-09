<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarisSatkerTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_inventaris_satker')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nup' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'nama_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Peralatan Kantor',
            ],
            'merk_tipe' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jumlah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Unit',
            ],
            'kondisi' => [
                'type'       => 'ENUM',
                'constraint' => ['baik', 'rusak_ringan', 'rusak_berat'],
                'default'    => 'baik',
            ],
            'lokasi_ruangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'default'    => 'Ruang Kantor Satker',
            ],
            'tahun_perolehan' => [
                'type'       => 'INT',
                'constraint' => 4,
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
        $this->forge->addKey('kode_barang');
        $this->forge->addKey('kategori');
        $this->forge->addKey('kondisi');
        $this->forge->createTable('trn_inventaris_satker', true);
    }

    public function down()
    {
        $this->forge->dropTable('trn_inventaris_satker', true);
    }
}
