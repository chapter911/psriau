<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRenewedFromIdToPinjamPakai extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
            if (! $db->fieldExists('renewed_from_id', 'trn_inventaris_pinjam_pakai')) {
                $forge->addColumn('trn_inventaris_pinjam_pakai', [
                    'renewed_from_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'default'    => null,
                        'after'      => 'inventaris_id',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
            if ($db->fieldExists('renewed_from_id', 'trn_inventaris_pinjam_pakai')) {
                $forge->dropColumn('trn_inventaris_pinjam_pakai', 'renewed_from_id');
            }
        }
    }
}
