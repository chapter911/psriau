<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKodeRegisterToInventarisSatkerTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_inventaris_satker')) {
            return;
        }

        if (! $this->db->fieldExists('kode_register', 'trn_inventaris_satker')) {
            $this->forge->addColumn('trn_inventaris_satker', [
                'kode_register' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'nup',
                ],
            ]);

            // Add index for fast QR/Barcode lookups
            $this->db->query('ALTER TABLE `trn_inventaris_satker` ADD INDEX `idx_kode_register` (`kode_register`)');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_inventaris_satker') && $this->db->fieldExists('kode_register', 'trn_inventaris_satker')) {
            $this->forge->dropColumn('trn_inventaris_satker', 'kode_register');
        }
    }
}
