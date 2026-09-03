<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdCardAndFotoIdCardToMstPegawai extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('mst_pegawai')) {
            $fields = [];

            if (! $this->db->fieldExists('id_card', 'mst_pegawai')) {
                $fields['id_card'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'email',
                ];
            }

            if (! $this->db->fieldExists('foto_id_card', 'mst_pegawai')) {
                $fields['foto_id_card'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'foto',
                ];
            }

            if (! empty($fields)) {
                $this->forge->addColumn('mst_pegawai', $fields);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('mst_pegawai')) {
            $columnsToDrop = [];

            if ($this->db->fieldExists('id_card', 'mst_pegawai')) {
                $columnsToDrop[] = 'id_card';
            }

            if ($this->db->fieldExists('foto_id_card', 'mst_pegawai')) {
                $columnsToDrop[] = 'foto_id_card';
            }

            if (! empty($columnsToDrop)) {
                $this->forge->dropColumn('mst_pegawai', $columnsToDrop);
            }
        }
    }
}
