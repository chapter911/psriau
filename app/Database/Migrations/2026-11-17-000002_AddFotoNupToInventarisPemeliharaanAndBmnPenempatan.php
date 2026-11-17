<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFotoNupToInventarisPemeliharaanAndBmnPenempatan extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();

        // 1. Tambah kolom foto_nup di trn_inventaris_pemeliharaan_foto
        if ($this->db->tableExists('trn_inventaris_pemeliharaan_foto')) {
            if (! $this->db->fieldExists('foto_nup', 'trn_inventaris_pemeliharaan_foto')) {
                $forge->addColumn('trn_inventaris_pemeliharaan_foto', [
                    'foto_nup' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true,
                        'after'      => 'foto_belakang',
                    ],
                ]);
            }
        }

        // 2. Tambah kolom foto_asli_5 dan foto_wm_5 di trn_bmn_penempatan
        if ($this->db->tableExists('trn_bmn_penempatan')) {
            $colsToAdd = [];
            if (! $this->db->fieldExists('foto_asli_5', 'trn_bmn_penempatan')) {
                $colsToAdd['foto_asli_5'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'foto_asli_4',
                ];
            }
            if (! $this->db->fieldExists('foto_wm_5', 'trn_bmn_penempatan')) {
                $colsToAdd['foto_wm_5'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'foto_wm_4',
                ];
            }
            if (! empty($colsToAdd)) {
                $forge->addColumn('trn_bmn_penempatan', $colsToAdd);
            }
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();

        if ($this->db->tableExists('trn_inventaris_pemeliharaan_foto')) {
            if ($this->db->fieldExists('foto_nup', 'trn_inventaris_pemeliharaan_foto')) {
                $forge->dropColumn('trn_inventaris_pemeliharaan_foto', 'foto_nup');
            }
        }

        if ($this->db->tableExists('trn_bmn_penempatan')) {
            if ($this->db->fieldExists('foto_asli_5', 'trn_bmn_penempatan')) {
                $forge->dropColumn('trn_bmn_penempatan', 'foto_asli_5');
            }
            if ($this->db->fieldExists('foto_wm_5', 'trn_bmn_penempatan')) {
                $forge->dropColumn('trn_bmn_penempatan', 'foto_wm_5');
            }
        }
    }
}
