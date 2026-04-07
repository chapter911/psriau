<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ResyncContohPaketDataRobust extends Migration
{
    public function up()
    {
        $tables = [
            'trn_kontrak_paket',
            'trn_kontrak_ki',
            'trn_kontrak_ki_pekerjaan_baphp',
        ];

        foreach ($tables as $table) {
            if (! $this->db->tableExists($table)) {
                return;
            }
        }

        $sqlPath = ROOTPATH . 'example/Database/contoh_paket.sql';
        if (! is_file($sqlPath)) {
            return;
        }

        $sql = (string) file_get_contents($sqlPath);
        if ($sql === '') {
            return;
        }

        $targets = array_fill_keys($tables, true);
        $statements = $this->extractInsertStatements($sql, array_keys($targets));

        foreach ($statements as $statement) {
            $rewritten = preg_replace('/^\s*INSERT\s+INTO\s+/i', 'REPLACE INTO ', $statement, 1);
            if (! is_string($rewritten) || trim($rewritten) === '') {
                continue;
            }

            try {
                $this->db->query($rewritten);
            } catch (\Throwable $e) {
                // Continue with other chunks to maximize imported rows.
            }
        }
    }

    public function down()
    {
        // No-op: synchronization migration.
    }

    /**
     * Extract full INSERT statements for the provided tables while respecting quoted strings.
     *
     * @param array<int, string> $tables
     * @return array<int, string>
     */
    private function extractInsertStatements(string $sql, array $tables): array
    {
        $result = [];
        $length = strlen($sql);
        $offset = 0;

        while ($offset < $length) {
            $insertPos = stripos($sql, 'INSERT INTO `', $offset);
            if ($insertPos === false) {
                break;
            }

            $tableStart = $insertPos + strlen('INSERT INTO `');
            $tableEnd = strpos($sql, '`', $tableStart);
            if ($tableEnd === false) {
                break;
            }

            $tableName = substr($sql, $tableStart, $tableEnd - $tableStart);
            if ($tableName === false || ! in_array($tableName, $tables, true)) {
                $offset = $tableEnd + 1;
                continue;
            }

            $statementEnd = $this->findStatementEnd($sql, $tableEnd + 1);
            if ($statementEnd === null) {
                break;
            }

            $statement = substr($sql, $insertPos, $statementEnd - $insertPos + 1);
            if ($statement !== false) {
                $result[] = trim($statement);
            }

            $offset = $statementEnd + 1;
        }

        return $result;
    }

    private function findStatementEnd(string $sql, int $start): ?int
    {
        $length = strlen($sql);
        $inSingleQuote = false;

        for ($index = $start; $index < $length; $index++) {
            $char = $sql[$index];
            $prev = $index > 0 ? $sql[$index - 1] : '';

            if ($char === "'" && $prev !== '\\') {
                $inSingleQuote = ! $inSingleQuote;
                continue;
            }

            if (! $inSingleQuote && $char === ';') {
                return $index;
            }
        }

        return null;
    }
}
