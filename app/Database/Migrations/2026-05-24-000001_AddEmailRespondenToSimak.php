<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailRespondenToSimak extends Migration
{
    public function up()
    {
        // Add columns to trn_kontrak_simak
        if (! $this->db->tableExists('trn_kontrak_simak')) {
            return;
        }

        $desiredFields = [
            'email_responden_1' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'default' => null,
                'after' => 'nilai_kontrak_jasa_konsultansi',
            ],
            'email_responden_2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'default' => null,
                'after' => 'email_responden_1',
            ],
        ];

        $forge = \Config\Database::forge();

        // Helper to add only missing fields to a table
        $addMissingFieldsToTable = function (string $tableName) use ($desiredFields, $forge) {
            try {
                $existing = array_column($this->db->getFieldData($tableName), 'name');
            } catch (\Exception $e) {
                $existing = [];
            }

            $toAdd = [];
            foreach ($desiredFields as $fname => $spec) {
                if (! in_array($fname, $existing, true)) {
                    $toAdd[$fname] = $spec;
                }
            }

            if ($toAdd !== []) {
                $forge->addColumn($tableName, $toAdd);
            }
        };

        // Add to main table
        $addMissingFieldsToTable('trn_kontrak_simak');

        // Add to konsultasi table if exists
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi')) {
            $addMissingFieldsToTable('trn_kontrak_simak_konsultasi');
        }

        // If legacy single column exists, copy its values into email_responden_1
        try {
            $fieldsData = $this->db->getFieldData('trn_kontrak_simak');
            $hasOld = false;
            foreach ($fieldsData as $f) {
                if ($f->name === 'email_responden') {
                    $hasOld = true;
                    break;
                }
            }

            if ($hasOld) {
                $this->db->query("UPDATE trn_kontrak_simak SET email_responden_1 = email_responden WHERE (email_responden_1 IS NULL OR email_responden_1 = '') AND (email_responden IS NOT NULL AND email_responden != '')");
            }

            if ($this->db->tableExists('trn_kontrak_simak_konsultasi')) {
                $fieldsData2 = $this->db->getFieldData('trn_kontrak_simak_konsultasi');
                $hasOld2 = false;
                foreach ($fieldsData2 as $f) {
                    if ($f->name === 'email_responden') {
                        $hasOld2 = true;
                        break;
                    }
                }
                if ($hasOld2) {
                    $this->db->query("UPDATE trn_kontrak_simak_konsultasi SET email_responden_1 = email_responden WHERE (email_responden_1 IS NULL OR email_responden_1 = '') AND (email_responden IS NOT NULL AND email_responden != '')");
                }
            }
        } catch (\Exception $e) {
            // ignore copy errors
        }
    }

    public function down()
    {
        $forge = \Config\Database::forge();
        if ($this->db->tableExists('trn_kontrak_simak')) {
            if (in_array('email_responden_1', array_column($this->db->getFieldData('trn_kontrak_simak'), 'name'))) {
                $forge->dropColumn('trn_kontrak_simak', 'email_responden_1');
            }
            if (in_array('email_responden_2', array_column($this->db->getFieldData('trn_kontrak_simak'), 'name'))) {
                $forge->dropColumn('trn_kontrak_simak', 'email_responden_2');
            }
        }
        if ($this->db->tableExists('trn_kontrak_simak_konsultasi')) {
            if (in_array('email_responden_1', array_column($this->db->getFieldData('trn_kontrak_simak_konsultasi'), 'name'))) {
                $forge->dropColumn('trn_kontrak_simak_konsultasi', 'email_responden_1');
            }
            if (in_array('email_responden_2', array_column($this->db->getFieldData('trn_kontrak_simak_konsultasi'), 'name'))) {
                $forge->dropColumn('trn_kontrak_simak_konsultasi', 'email_responden_2');
            }
        }
    }
}
