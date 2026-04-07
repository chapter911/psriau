<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ImportContohPaketTables extends Migration
{
    public function up()
    {
        $sqlPath = ROOTPATH . 'example/Database/contoh_paket.sql';
        if (! is_file($sqlPath)) {
            return;
        }

        $sql = (string) file_get_contents($sqlPath);
        if ($sql === '') {
            return;
        }

        preg_match_all('/CREATE TABLE `([^`]+)`\s*\((.*?)\) ENGINE=.*?;/si', $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $table = (string) ($match[1] ?? '');
            if ($table === '' || $this->db->tableExists($table)) {
                continue;
            }

            $statement = (string) ($match[0] ?? '');
            if ($statement === '') {
                continue;
            }

            $this->db->query($statement);
        }

        $this->applyPrimaryKeysAndAutoIncrement();
    }

    public function down()
    {
        $tables = [
            'trn_kontrak_ki_pekerjaan_baphp',
            'trn_kontrak_ki',
            'trn_kontrak_paket',
        ];

        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }

    private function applyPrimaryKeysAndAutoIncrement(): void
    {
        if ($this->db->tableExists('trn_kontrak_ki')) {
            $this->safeQuery('ALTER TABLE `trn_kontrak_ki` ADD PRIMARY KEY (`id`)');
            $this->safeQuery('ALTER TABLE `trn_kontrak_ki` ADD UNIQUE KEY `unik` (`nomor_kontrak`) USING BTREE');
            $this->safeQuery('ALTER TABLE `trn_kontrak_ki` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT');
        }

        if ($this->db->tableExists('trn_kontrak_ki_pekerjaan_baphp')) {
            $this->safeQuery('ALTER TABLE `trn_kontrak_ki_pekerjaan_baphp` ADD PRIMARY KEY (`id`)');
            $this->safeQuery('ALTER TABLE `trn_kontrak_ki_pekerjaan_baphp` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT "Primary Key"');
        }

        if ($this->db->tableExists('trn_kontrak_paket')) {
            $this->safeQuery('ALTER TABLE `trn_kontrak_paket` ADD PRIMARY KEY (`id`)');
            $this->safeQuery('ALTER TABLE `trn_kontrak_paket` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    private function safeQuery(string $sql): void
    {
        try {
            $this->db->query($sql);
        } catch (\Throwable $e) {
            // Ignore duplicate key/column errors for idempotency.
        }
    }
}
