<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeNoSuratNullableInPinjamPakai extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pinjam_pakai') && $db->fieldExists('no_surat', 'trn_inventaris_pinjam_pakai')) {
            $fields = [
                'no_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'default'    => null,
                ],
            ];
            $forge->modifyColumn('trn_inventaris_pinjam_pakai', $fields);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pinjam_pakai') && $db->fieldExists('no_surat', 'trn_inventaris_pinjam_pakai')) {
            $fields = [
                'no_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
            ];
            $forge->modifyColumn('trn_inventaris_pinjam_pakai', $fields);
        }
    }
}
