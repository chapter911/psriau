<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeNilaiKontrakToBigint extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_simak');
        $fieldExists = false;
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === 'nilai_kontrak') {
                $fieldExists = true;
                break;
            }
        }

        if ($fieldExists) {
            $this->forge->modifyColumn('trn_kontrak_simak', [
                'nilai_kontrak' => [
                    'type' => 'BIGINT',
                    'default' => 0,
            ],
            ]);
        }

        if ($this->db->tableExists('trn_kontrak_simak_add_on')) {
            $fields = $this->db->getFieldData('trn_kontrak_simak_add_on');
            $fieldExists = false;
            foreach ($fields as $field) {
                if (strtolower((string) $field->name) === 'nilai_add_on') {
                    $fieldExists = true;
                    break;
                }
            }

            if ($fieldExists) {
                $this->forge->modifyColumn('trn_kontrak_simak_add_on', [
                    'nilai_add_on' => [
                        'type' => 'BIGINT',
                        'default' => 0,
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_simak');
        $fieldExists = false;
        foreach ($fields as $field) {
            if (strtolower((string) $field->name) === 'nilai_kontrak') {
                $fieldExists = true;
                break;
            }
        }

        if ($fieldExists) {
            $this->forge->modifyColumn('trn_kontrak_simak', [
                'nilai_kontrak' => [
                    'type' => 'DECIMAL',
                    'constraint' => '18,2',
                    'default' => 0,
                ],
            ]);
        }

        if ($this->db->tableExists('trn_kontrak_simak_add_on')) {
            $fields = $this->db->getFieldData('trn_kontrak_simak_add_on');
            $fieldExists = false;
            foreach ($fields as $field) {
                if (strtolower((string) $field->name) === 'nilai_add_on') {
                    $fieldExists = true;
                    break;
                }
            }

            if ($fieldExists) {
                $this->forge->modifyColumn('trn_kontrak_simak_add_on', [
                    'nilai_add_on' => [
                        'type' => 'DECIMAL',
                        'constraint' => '18,2',
                        'default' => 0,
                    ],
                ]);
            }
        }
    }
}
