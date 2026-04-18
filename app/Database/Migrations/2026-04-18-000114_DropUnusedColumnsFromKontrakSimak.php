<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropUnusedColumnsFromKontrakSimak extends Migration
{
    private array $columns = [
        'dokumen_adm_kontrak' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
        'dokumen_adm_spmk' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
        'dokumen_adm_baphp' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
        'dokumen_adm_bast' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
        'dokumen_adm_invoice' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
        'kelengkapan_dokumen_administrasi' => [
            'type' => 'DECIMAL',
            'constraint' => '5,2',
            'default' => 0,
        ],
    ];

    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $existing = [];
        foreach ($this->db->getFieldData('trn_kontrak_simak') as $field) {
            $existing[] = strtolower((string) ($field->name ?? ''));
        }

        foreach (array_keys($this->columns) as $column) {
            if (in_array(strtolower($column), $existing, true)) {
                $this->forge->dropColumn('trn_kontrak_simak', $column);
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $existing = [];
        foreach ($this->db->getFieldData('trn_kontrak_simak') as $field) {
            $existing[] = strtolower((string) ($field->name ?? ''));
        }

        foreach ($this->columns as $column => $definition) {
            if (! in_array(strtolower($column), $existing, true)) {
                $this->forge->addColumn('trn_kontrak_simak', [
                    $column => $definition,
                ]);
            }
        }
    }
}
