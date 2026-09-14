<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarisPinjamPakaiItemTable extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        // 1. Buat tabel trn_inventaris_pinjam_pakai_item jika belum ada
        if (! $db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'pinjam_pakai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'inventaris_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'kondisi_pinjam' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'baik',
                    'null'       => false,
                ],
                'kondisi_kembali' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                ],
                'catatan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['dipinjam', 'dikembalikan'],
                    'default'    => 'dipinjam',
                    'null'       => false,
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

            $forge->addKey('id', true);
            $forge->addKey('pinjam_pakai_id');
            $forge->addKey('inventaris_id');
            $forge->addKey('status');

            $forge->createTable('trn_inventaris_pinjam_pakai_item', true);
        }

        // 2. Backfill data lama dari trn_inventaris_pinjam_pakai ke trn_inventaris_pinjam_pakai_item jika belum ada
        if ($db->tableExists('trn_inventaris_pinjam_pakai') && $db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $existingCount = $db->table('trn_inventaris_pinjam_pakai_item')->countAllResults();
            if ($existingCount === 0) {
                $db->query("
                    INSERT INTO trn_inventaris_pinjam_pakai_item 
                        (pinjam_pakai_id, inventaris_id, kondisi_pinjam, kondisi_kembali, status, created_at, updated_at)
                    SELECT 
                        id, 
                        inventaris_id, 
                        COALESCE(kondisi_pinjam, 'baik'), 
                        kondisi_kembali, 
                        COALESCE(status, 'dipinjam'), 
                        created_at, 
                        updated_at
                    FROM trn_inventaris_pinjam_pakai
                    WHERE inventaris_id IS NOT NULL AND inventaris_id > 0
                ");
            }
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();
        if ($this->db->tableExists('trn_inventaris_pinjam_pakai_item')) {
            $forge->dropTable('trn_inventaris_pinjam_pakai_item', true);
        }
    }
}
