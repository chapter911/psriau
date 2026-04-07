<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ImportContohPaketData extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_paket') || ! $this->db->tableExists('trn_kontrak_ki') || ! $this->db->tableExists('trn_kontrak_ki_pekerjaan_baphp')) {
            return;
        }

        $sqlPath = ROOTPATH . 'example/Database/contoh_paket.sql';
        if (! is_file($sqlPath)) {
            return;
        }

        $sql = (string) file_get_contents($sqlPath);
        if ($sql === '') {
            return;
        }

        $targets = [
            'trn_kontrak_paket',
            'trn_kontrak_ki',
            'trn_kontrak_ki_pekerjaan_baphp',
        ];

        foreach ($targets as $table) {
            $count = (int) $this->db->table($table)->countAllResults();
            if ($count > 0) {
                continue;
            }

            $this->importTableData($sql, $table);
        }
    }

    public function down()
    {
        $targets = [
            'trn_kontrak_ki_pekerjaan_baphp',
            'trn_kontrak_ki',
            'trn_kontrak_paket',
        ];

        foreach ($targets as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->table($table)->truncate();
            }
        }
    }

    private function importTableData(string $sql, string $table): void
    {
        $pattern = '/INSERT INTO `'. preg_quote($table, '/') . '`\s*\((.*?)\)\s*VALUES\s*(.*?);/si';
        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $statement = trim((string) ($match[0] ?? ''));
            if ($statement === '') {
                continue;
            }

            $statement = preg_replace('/^INSERT INTO/i', 'REPLACE INTO', $statement, 1);

            try {
                $this->db->query((string) $statement);
            } catch (\Throwable $e) {
                // Skip invalid rows and continue importing other chunks.
            }
        }
    }
}
