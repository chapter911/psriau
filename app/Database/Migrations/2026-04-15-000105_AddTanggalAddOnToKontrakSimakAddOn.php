<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggalAddOnToKontrakSimakAddOn extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_simak_add_on')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_simak_add_on');
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === 'tanggal_add_on') {
                return;
            }
        }

        $this->forge->addColumn('trn_kontrak_simak_add_on', [
            'tanggal_add_on' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'nilai_add_on',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_simak_add_on')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_simak_add_on');
        $exists = false;
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === 'tanggal_add_on') {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $this->forge->dropColumn('trn_kontrak_simak_add_on', 'tanggal_add_on');
        }
    }
}
