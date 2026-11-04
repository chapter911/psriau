<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class MoveUnassignedInventarisToBelumDitentukan extends Migration
{
    public function up()
    {
        $db = $this->db;
        $forge = Database::forge();

        if ($db->tableExists('trn_inventaris_satker')) {
            // 1. Ubah default value kolom peruntukan menjadi 'belum_ditentukan'
            $forge->modifyColumn('trn_inventaris_satker', [
                'peruntukan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'belum_ditentukan',
                    'null'       => false,
                ],
            ]);

            // 2. Pindahkan aset kantor yang belum masuk ruangan ke 'belum_ditentukan'
            // Syarat: peruntukan 'kantor' atau kosong/NULL, dan belum memiliki ruangan_id > 0
            $db->table('trn_inventaris_satker')
                ->groupStart()
                    ->where('peruntukan', 'kantor')
                    ->orWhere('peruntukan IS NULL', null, false)
                    ->orWhere('peruntukan', '')
                ->groupEnd()
                ->groupStart()
                    ->where('ruangan_id IS NULL', null, false)
                    ->orWhere('ruangan_id', 0)
                    ->orWhere('lokasi_ruangan', 'Belum berlokasi')
                    ->orWhere('lokasi_ruangan IS NULL', null, false)
                    ->orWhere('TRIM(lokasi_ruangan)', '')
                ->groupEnd()
                ->update([
                    'peruntukan'     => 'belum_ditentukan',
                    'lokasi_ruangan' => 'Belum berlokasi',
                    'ruangan_id'     => null,
                ]);
        }
    }

    public function down()
    {
        $db = $this->db;
        $forge = Database::forge();

        if ($db->tableExists('trn_inventaris_satker')) {
            // 1. Kembalikan default value kolom peruntukan menjadi 'kantor'
            $forge->modifyColumn('trn_inventaris_satker', [
                'peruntukan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'kantor',
                    'null'       => false,
                ],
            ]);

            // 2. Kembalikan peruntukan 'belum_ditentukan' menjadi 'kantor'
            $db->table('trn_inventaris_satker')
                ->where('peruntukan', 'belum_ditentukan')
                ->update([
                    'peruntukan' => 'kantor',
                ]);
        }
    }
}

