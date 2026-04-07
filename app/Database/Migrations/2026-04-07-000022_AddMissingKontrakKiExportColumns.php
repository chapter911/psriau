<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingKontrakKiExportColumns extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_ki')) {
            return;
        }

        $existing = [];
        foreach ($this->db->getFieldData('trn_kontrak_ki') as $field) {
            $existing[strtolower((string) $field->name)] = true;
        }

        $columns = [];

        if (! isset($existing['tanggal_spmk'])) {
            $columns['tanggal_spmk'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! isset($existing['pendidikan'])) {
            $columns['pendidikan'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if (! isset($existing['sertifikat'])) {
            $columns['sertifikat'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ];
        }

        if ($columns !== []) {
            $this->forge->addColumn('trn_kontrak_ki', $columns);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_ki')) {
            return;
        }

        $existing = [];
        foreach ($this->db->getFieldData('trn_kontrak_ki') as $field) {
            $existing[strtolower((string) $field->name)] = true;
        }

        $drop = [];
        foreach (['tanggal_spmk', 'pendidikan', 'sertifikat'] as $column) {
            if (isset($existing[$column])) {
                $drop[] = $column;
            }
        }

        if ($drop !== []) {
            $this->forge->dropColumn('trn_kontrak_ki', $drop);
        }
    }
}
