<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropKopSuratFromKontrakKi extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_ki')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_ki');
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === 'kop_surat_id') {
                $this->forge->dropColumn('trn_kontrak_ki', 'kop_surat_id');
                break;
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_ki')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_ki');
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === 'kop_surat_id') {
                return;
            }
        }

        $this->forge->addColumn('trn_kontrak_ki', [
            'kop_surat_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'paket',
            ],
        ]);
    }
}
