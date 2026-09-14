<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKopSuratIdToPinjamPakai extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = \Config\Database::forge();

        if ($db->tableExists('trn_inventaris_pinjam_pakai')) {
            if (! $db->fieldExists('kop_surat_id', 'trn_inventaris_pinjam_pakai')) {
                $forge->addColumn('trn_inventaris_pinjam_pakai', [
                    'kop_surat_id' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                        'null'       => true,
                        'after'      => 'no_surat',
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
            if ($db->fieldExists('kop_surat_id', 'trn_inventaris_pinjam_pakai')) {
                $forge->dropColumn('trn_inventaris_pinjam_pakai', 'kop_surat_id');
            }
        }
    }
}
