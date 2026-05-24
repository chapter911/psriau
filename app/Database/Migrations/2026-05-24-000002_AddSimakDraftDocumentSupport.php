<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSimakDraftDocumentSupport extends Migration
{
    public function up()
    {
        $masterTables = [
            'mst_simak_konstruksi_item',
            'mst_simak_konsultasi_item',
        ];

        foreach ($masterTables as $table) {
            if ($this->db->tableExists($table) && ! $this->db->fieldExists('has_draft', $table)) {
                $this->forge->addColumn($table, [
                    'has_draft' => [
                        'type' => 'TINYINT',
                        'constraint' => 1,
                        'unsigned' => true,
                        'default' => 0,
                        'after' => 'has_question',
                    ],
                ]);
            }
        }

        $documentTables = [
            'trn_kontrak_simak_verifikasi_dokumen',
            'trn_kontrak_simak_konsultasi_verifikasi_dokumen',
        ];

        foreach ($documentTables as $table) {
            if ($this->db->tableExists($table) && ! $this->db->fieldExists('tipe_dokumen', $table)) {
                $this->forge->addColumn($table, [
                    'tipe_dokumen' => [
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'null' => true,
                        'after' => 'file_size',
                    ],
                ]);
            }
        }

        foreach ($masterTables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('has_draft', $table)) {
                $this->db->query('UPDATE ' . $this->db->protectIdentifiers($table, true) . ' SET has_draft = 0 WHERE has_draft IS NULL');
            }
        }

        foreach ($documentTables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('tipe_dokumen', $table)) {
                $this->db->query("UPDATE " . $this->db->protectIdentifiers($table, true) . " SET tipe_dokumen = 'final' WHERE tipe_dokumen IS NULL OR tipe_dokumen = ''");
            }
        }
    }

    public function down()
    {
        $documentTables = [
            'trn_kontrak_simak_verifikasi_dokumen',
            'trn_kontrak_simak_konsultasi_verifikasi_dokumen',
        ];

        foreach ($documentTables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('tipe_dokumen', $table)) {
                $this->forge->dropColumn($table, 'tipe_dokumen');
            }
        }

        $masterTables = [
            'mst_simak_konstruksi_item',
            'mst_simak_konsultasi_item',
        ];

        foreach ($masterTables as $table) {
            if ($this->db->tableExists($table) && $this->db->fieldExists('has_draft', $table)) {
                $this->forge->dropColumn($table, 'has_draft');
            }
        }
    }
}