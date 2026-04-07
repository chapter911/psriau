<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKontrakAuditSoftDelete extends Migration
{
    public function up()
    {
        $this->addAuditColumns('trn_kontrak_paket');
        $this->addAuditColumns('trn_kontrak_ki');
        $this->addAuditColumns('trn_kontrak_ki_pekerjaan_baphp');

        if ($this->db->tableExists('trn_kontrak_paket')) {
            $this->safeQuery('CREATE INDEX idx_trn_kontrak_paket_deleted_at ON trn_kontrak_paket (deleted_at)');
            $this->safeQuery('CREATE INDEX idx_trn_kontrak_paket_created_date ON trn_kontrak_paket (created_date)');
        }

        if ($this->db->tableExists('trn_kontrak_ki')) {
            $this->safeQuery('CREATE INDEX idx_trn_kontrak_ki_paket_deleted ON trn_kontrak_ki (paket, deleted_at)');
            $this->safeQuery('CREATE INDEX idx_trn_kontrak_ki_tahun_anggaran ON trn_kontrak_ki (tahun_anggaran)');
            $this->safeQuery('CREATE INDEX idx_trn_kontrak_ki_kategori ON trn_kontrak_ki (kategori)');
        }

        if ($this->db->tableExists('trn_kontrak_ki_pekerjaan_baphp')) {
            $this->safeQuery('CREATE INDEX idx_trn_kontrak_ki_pekerjaan_paket_deleted ON trn_kontrak_ki_pekerjaan_baphp (id_kontrak_paket, deleted_at)');
        }
    }

    public function down()
    {
        $this->dropAuditColumns('trn_kontrak_ki_pekerjaan_baphp');
        $this->dropAuditColumns('trn_kontrak_ki');
        $this->dropAuditColumns('trn_kontrak_paket');

        $this->safeQuery('DROP INDEX idx_trn_kontrak_paket_deleted_at ON trn_kontrak_paket');
        $this->safeQuery('DROP INDEX idx_trn_kontrak_paket_created_date ON trn_kontrak_paket');
        $this->safeQuery('DROP INDEX idx_trn_kontrak_ki_paket_deleted ON trn_kontrak_ki');
        $this->safeQuery('DROP INDEX idx_trn_kontrak_ki_tahun_anggaran ON trn_kontrak_ki');
        $this->safeQuery('DROP INDEX idx_trn_kontrak_ki_kategori ON trn_kontrak_ki');
        $this->safeQuery('DROP INDEX idx_trn_kontrak_ki_pekerjaan_paket_deleted ON trn_kontrak_ki_pekerjaan_baphp');
    }

    private function addAuditColumns(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldData($table);
        $existing = array_map(static fn ($field) => strtolower($field->name), $fields);

        if (! in_array('created_at', $existing, true)) {
            $this->forge->addColumn($table, [
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }

        if (! in_array('updated_at', $existing, true)) {
            $this->forge->addColumn($table, [
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }

        if (! in_array('updated_by', $existing, true)) {
            $this->forge->addColumn($table, [
                'updated_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
        }

        if (! in_array('deleted_at', $existing, true)) {
            $this->forge->addColumn($table, [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }

        if (! in_array('deleted_by', $existing, true)) {
            $this->forge->addColumn($table, [
                'deleted_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
        }
    }

    private function dropAuditColumns(string $table): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldData($table);
        $existing = array_map(static fn ($field) => strtolower($field->name), $fields);

        foreach (['created_at', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by'] as $column) {
            if (in_array($column, $existing, true)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }

    private function safeQuery(string $sql): void
    {
        try {
            $this->db->query($sql);
        } catch (\Throwable $e) {
            // Ignore when index does not exist or already exists.
        }
    }
}
