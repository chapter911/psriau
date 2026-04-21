<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterSimakKonsultasiItem extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('mst_simak_konsultasi_item')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'parent_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'row_no' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'display_no' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                ],
                'uraian' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'bentuk_dokumen' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'referensi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'kriteria_administrasi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'kriteria_substansi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'sumber_dokumen_hasil_integrasi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'row_kind' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'question',
                ],
                'has_question' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'ordering' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'is_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('row_no');
            $this->forge->addKey('parent_id');
            $this->forge->addKey('ordering');
            $this->forge->createTable('mst_simak_konsultasi_item', true);
        }

        $this->seedFromTemplateIfEmpty();
    }

    public function down()
    {
        $this->forge->dropTable('mst_simak_konsultasi_item', true);
    }

    private function seedFromTemplateIfEmpty(): void
    {
        $count = (int) $this->db->table('mst_simak_konsultasi_item')->countAllResults();
        if ($count > 0) {
            return;
        }

        $templateItems = $this->parseTemplateItems();
        if ($templateItems === []) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $stackByIndent = [];
        $orderingCounters = [];

        foreach ($templateItems as $item) {
            $indent = max(0, (int) ($item['indent_level'] ?? 0));
            $parentId = null;
            if ($indent > 0) {
                $parentId = $stackByIndent[$indent - 1] ?? null;
            }

            $orderingKey = $parentId === null ? 'root' : ('p:' . $parentId);
            $orderingCounters[$orderingKey] = (int) (($orderingCounters[$orderingKey] ?? 0) + 1);

            $rowKind = $this->resolveRowKind($item);
            $hasQuestion = ((bool) ($item['is_leaf'] ?? false)) ? 1 : 0;

            $this->db->table('mst_simak_konsultasi_item')->insert([
                'parent_id' => $parentId,
                'row_no' => (int) ($item['row_no'] ?? 0),
                'display_no' => trim((string) ($item['display_no'] ?? '')),
                'uraian' => trim((string) ($item['uraian'] ?? '')),
                'bentuk_dokumen' => trim((string) ($item['bentuk_dokumen'] ?? '')),
                'referensi' => trim((string) ($item['referensi'] ?? '')),
                'kriteria_administrasi' => trim((string) ($item['kriteria_administrasi'] ?? '')),
                'kriteria_substansi' => trim((string) ($item['kriteria_substansi'] ?? '')),
                'sumber_dokumen_hasil_integrasi' => trim((string) ($item['sumber_dokumen_hasil_integrasi'] ?? '')),
                'row_kind' => $rowKind,
                'has_question' => $hasQuestion,
                'ordering' => (int) $orderingCounters[$orderingKey],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $newId = (int) $this->db->insertID();
            $stackByIndent[$indent] = $newId;
            foreach (array_keys($stackByIndent) as $level) {
                if ($level > $indent) {
                    unset($stackByIndent[$level]);
                }
            }
        }
    }

    private function resolveRowKind(array $item): string
    {
        $rowType = (string) ($item['row_type'] ?? 'detail_text');
        $isHeader = (bool) ($item['is_header'] ?? false);
        $isLeaf = (bool) ($item['is_leaf'] ?? false);

        if ($rowType === 'separator') {
            return 'separator';
        }

        if ($isHeader) {
            return 'section';
        }

        if (! $isLeaf) {
            return 'group';
        }

        return 'question';
    }

    private function parseTemplateItems(): array
    {
        $filePath = WRITEPATH . 'templates/contoh_simak.xlsx';
        if (! is_file($filePath)) {
            return [];
        }

        if (! class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            return [];
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName('Daftar SIMAK JK (>100juta)');
            if ($sheet === null) {
                return [];
            }

            $items = [];
            $currentSectionKey = '';
            $currentSectionTitle = '';
            $currentSubsectionKey = '';
            $lastSubsectionLetter = [];
            $sectionCounter = 0;
            $seenSectionNo = [];
            $highestRow = (int) $sheet->getHighestRow();
            $lastRelevantRow = 24;

            for ($scanRow = 24; $scanRow <= $highestRow; $scanRow++) {
                $scanColC = trim((string) $sheet->getCell('C' . $scanRow)->getFormattedValue());
                $scanColD = trim((string) $sheet->getCell('D' . $scanRow)->getFormattedValue());
                if ($scanColC !== '' || $scanColD !== '') {
                    $lastRelevantRow = $scanRow;
                }
            }

            for ($row = 24; $row <= $lastRelevantRow; $row++) {
                $colB = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
                $colC = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
                $colD = trim((string) $sheet->getCell('D' . $row)->getFormattedValue());
                $colE = trim((string) $sheet->getCell('E' . $row)->getFormattedValue());
                $colF = trim((string) $sheet->getCell('F' . $row)->getFormattedValue());
                $colG = trim((string) $sheet->getCell('G' . $row)->getFormattedValue());
                $colH = trim((string) $sheet->getCell('H' . $row)->getFormattedValue());
                $colI = trim((string) $sheet->getCell('I' . $row)->getFormattedValue());

                $upperColC = strtoupper($colC);
                $upperColD = strtoupper($colD);

                if ($upperColC === 'REKAPITULASI DOKUMEN'
                    || $upperColD === 'NILAI'
                    || str_starts_with($upperColD, 'DIVERIFIKASI PADA TANGGAL')
                    || str_starts_with($upperColD, 'PPK / TIM PPK')) {
                    break;
                }

                if ($row === 24 && strtolower($colB) === 'no.' && strtolower($colC) === 'tahapan') {
                    continue;
                }

                if ($colC === '' && $colD === '') {
                    continue;
                }

                $displayNo = '';
                $indentLevel = 0;
                $rowType = 'detail';
                $rowPriority = 4;
                $uraian = $colD;
                $isSectionHeader = false;

                if ($colD === '' && preg_match('/^([A-Z])(?:[\.|\)])?$/', $colB, $sectionMatches) && $colC !== '') {
                    $isSectionHeader = true;
                    $displayNo = (string) ($sectionMatches[1] ?? $colB);
                    $sectionCounter++;
                    if ($displayNo === '' || isset($seenSectionNo[$displayNo])) {
                        $normalizedNo = chr(ord('A') + max(0, $sectionCounter - 1));
                        if (preg_match('/^[A-Z]$/', $normalizedNo)) {
                            $displayNo = $normalizedNo;
                        }
                    }
                    $seenSectionNo[$displayNo] = true;
                    $uraian = $colC;
                    $indentLevel = 0;
                    $rowPriority = 0;
                    $currentSectionKey = $displayNo . '_' . $row;
                    $currentSectionTitle = $uraian;
                    $currentSubsectionKey = '';
                    $rowType = 'section_header';
                } elseif ($colD === '' && $colB !== '' && $colC !== '') {
                    $displayNo = $colB;
                    $uraian = $colC;
                    $indentLevel = 1;
                    $rowPriority = 1;
                    $rowType = 'subsection_header';
                    $currentSubsectionKey = $currentSectionKey . '|sub_' . $row;
                } elseif ($colD === '' && $colB === '' && $colC !== '') {
                    $displayNo = '';
                    $uraian = $colC;
                    $indentLevel = 2;
                    $rowPriority = 4;
                    $rowType = 'detail_text';
                } elseif ($colC !== '' && preg_match('/^[a-zA-Z]$/', $colC) && $colD !== '') {
                    $displayNo = $colC;
                    $uraian = $colD;
                    $indentLevel = 1;
                    $rowPriority = 2;
                    $rowType = 'subsection_item';

                    if ($currentSubsectionKey !== '') {
                        $normalizedLetter = strtolower($displayNo);
                        $lastLetter = $lastSubsectionLetter[$currentSubsectionKey] ?? '';
                        if ($lastLetter !== '' && $normalizedLetter <= $lastLetter) {
                            $nextCode = ord($lastLetter) + 1;
                            if ($nextCode >= ord('a') && $nextCode <= ord('z')) {
                                $normalizedLetter = chr($nextCode);
                            }
                        }
                        $displayNo = $normalizedLetter;
                        $lastSubsectionLetter[$currentSubsectionKey] = $normalizedLetter;
                    }
                } elseif ($colD !== '' && preg_match('/^([0-9]+|[a-zA-Z])([\.|\)]|\s)+\s*(.+)$/u', $colD, $matches)) {
                    $displayNo = $matches[1];
                    $uraian = $matches[3];
                    $indentLevel = 2;
                    $rowPriority = 3;
                    $rowType = 'detail_numbered';
                } elseif ($colD !== '') {
                    $displayNo = '';
                    $uraian = $colD;
                    $indentLevel = 2;
                    $rowPriority = 4;
                    $rowType = 'detail_text';
                }

                if ($rowType === 'detail_text' && preg_match('/^untuk\s+jasa\s+konsultansi/i', $uraian)) {
                    $rowType = 'separator';
                    $rowPriority = 1;
                }

                if ($rowType === 'detail_text' && preg_match('/^kondisi\s+khusus$/i', $uraian)) {
                    $rowType = 'separator';
                    $rowPriority = 1;
                }

                if ($rowType === 'separator' && $currentSubsectionKey !== '') {
                    unset($lastSubsectionLetter[$currentSubsectionKey]);
                }

                $items[] = [
                    'row_no' => $row,
                    'display_no' => $displayNo,
                    'uraian' => $uraian,
                    'bentuk_dokumen' => $colE,
                    'referensi' => $colF,
                    'kriteria_administrasi' => $colG,
                    'kriteria_substansi' => $colH,
                    'sumber_dokumen_hasil_integrasi' => $colI,
                    'is_header' => $isSectionHeader,
                    'indent_level' => $indentLevel,
                    'row_type' => $rowType,
                    'row_priority' => $rowPriority,
                    'section_key' => $currentSectionKey,
                    'section_title' => $currentSectionTitle,
                ];
            }

            $totalItems = count($items);
            for ($index = 0; $index < $totalItems; $index++) {
                $current = $items[$index];
                $activeSectionKey = (string) ($current['section_key'] ?? '');
                $currentIsHeader = (bool) ($current['is_header'] ?? false);
                $currentPriority = (int) ($current['row_priority'] ?? 4);

                $hasChildren = false;
                for ($nextIndex = $index + 1; $nextIndex < $totalItems; $nextIndex++) {
                    $next = $items[$nextIndex];
                    if ((string) ($next['section_key'] ?? '') !== $activeSectionKey) {
                        break;
                    }

                    $nextPriority = (int) ($next['row_priority'] ?? 4);
                    if ($nextPriority <= $currentPriority) {
                        break;
                    }

                    $hasChildren = true;
                    break;
                }

                $items[$index]['has_children'] = $hasChildren;
                $items[$index]['is_leaf'] = ! $hasChildren && ! $currentIsHeader;

                if (($items[$index]['row_type'] ?? '') === 'separator') {
                    $items[$index]['has_children'] = false;
                    $items[$index]['is_leaf'] = false;
                }
            }

            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
