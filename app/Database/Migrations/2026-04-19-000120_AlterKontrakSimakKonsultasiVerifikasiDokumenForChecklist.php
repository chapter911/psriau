<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterKontrakSimakKonsultasiVerifikasiDokumenForChecklist extends Migration
{
    public function up()
    {
        $table = 'trn_kontrak_simak_konsultasi_verifikasi_dokumen';
        if (! $this->db->tableExists($table)) {
            return;
        }

        $existingColumns = array_map(static function ($row): string {
            return (string) ($row->name ?? '');
        }, $this->db->getFieldData($table));

        $fields = [];
        if (! in_array('row_no', $existingColumns, true)) {
            $fields['row_no'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'simak_id',
            ];
        }
        if (! in_array('kode', $existingColumns, true)) {
            $fields['kode'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'row_no',
            ];
        }
        if (! in_array('uraian', $existingColumns, true)) {
            $fields['uraian'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'kode',
            ];
        }
        if (! in_array('kelengkapan_dokumen', $existingColumns, true)) {
            $fields['kelengkapan_dokumen'] = [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'after' => 'uraian',
            ];
        }
        if (! in_array('verifikasi_ki', $existingColumns, true)) {
            $fields['verifikasi_ki'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'kelengkapan_dokumen',
            ];
        }
        if (! in_array('keterangan', $existingColumns, true)) {
            $fields['keterangan'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'verifikasi_ki',
            ];
        }
        if (! in_array('pic', $existingColumns, true)) {
            $fields['pic'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'keterangan',
            ];
        }
        if (! in_array('file_original_name', $existingColumns, true)) {
            $fields['file_original_name'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'pic',
            ];
        }
        if (! in_array('file_stored_name', $existingColumns, true)) {
            $fields['file_stored_name'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'file_original_name',
            ];
        }
        if (! in_array('file_relative_path', $existingColumns, true)) {
            $fields['file_relative_path'] = [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'file_stored_name',
            ];
        }
        if (! in_array('file_mime', $existingColumns, true)) {
            $fields['file_mime'] = [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
                'after' => 'file_relative_path',
            ];
        }
        if (! in_array('file_size', $existingColumns, true)) {
            $fields['file_size'] = [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'after' => 'file_mime',
            ];
        }
        if (! in_array('deleted_by', $existingColumns, true)) {
            $fields['deleted_by'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'deleted_at',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn($table, $fields);
        }

        $existingColumns = array_map(static function ($row): string {
            return (string) ($row->name ?? '');
        }, $this->db->getFieldData($table));
        if (in_array('simak_id', $existingColumns, true) && in_array('row_no', $existingColumns, true)) {
            try {
                $this->db->query('CREATE INDEX idx_simak_konsultasi_verifikasi_dokumen_simak_row ON ' . $table . ' (simak_id, row_no)');
            } catch (\Throwable $e) {
                // Ignore when index already exists.
            }
        }
    }

    public function down()
    {
        // Intentionally left blank to avoid destructive schema rollback in production data.
    }
}
