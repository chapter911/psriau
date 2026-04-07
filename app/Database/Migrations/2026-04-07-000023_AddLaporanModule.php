<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLaporanModule extends Migration
{
    public function up()
    {
        $this->createTables();
        $this->seedMenus();
    }

    public function down()
    {
        $this->removeMenus();

        if ($this->db->tableExists('laporan_mingguan_reports')) {
            $this->forge->dropTable('laporan_mingguan_reports', true);
        }

        if ($this->db->tableExists('laporan_harian_reports')) {
            $this->forge->dropTable('laporan_harian_reports', true);
        }

        if ($this->db->tableExists('laporan_harian_titles')) {
            $this->forge->dropTable('laporan_harian_titles', true);
        }
    }

    private function createTables(): void
    {
        if (! $this->db->tableExists('laporan_harian_titles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
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
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('laporan_harian_titles');
        }

        if (! $this->db->tableExists('laporan_harian_reports')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'title_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'report_date' => [
                    'type' => 'DATE',
                ],
                'sections_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'personil_pekerja' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'personil_tukang' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'cuaca_cerah' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'cuaca_hujan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'photo_paths_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
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
            $this->forge->addKey('title_id');
            $this->forge->addKey('report_date');
            $this->forge->createTable('laporan_harian_reports');
        }

        if (! $this->db->tableExists('laporan_mingguan_reports')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'title_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'period_start' => [
                    'type' => 'DATE',
                ],
                'period_end' => [
                    'type' => 'DATE',
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'file_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'file_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
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
            $this->forge->addKey('title_id');
            $this->forge->addKey('period_start');
            $this->forge->addKey('period_end');
            $this->forge->createTable('laporan_mingguan_reports');
        }
    }

    private function seedMenus(): void
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $parentId = $this->findOrCreateLv1Menu('Laporan', 'fas fa-clipboard-list');
        if ($parentId === null) {
            return;
        }

        $harianId = $this->upsertLv2ByLink('admin/laporan/harian', 'Harian', 'far fa-calendar-alt', $parentId);
        $mingguanId = $this->upsertLv2ByLink('admin/laporan/mingguan', 'Mingguan', 'far fa-file-alt', $parentId);

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($parentId);
            $this->ensureAksesForMenu($harianId);
            $this->ensureAksesForMenu($mingguanId);
        }
    }

    private function removeMenus(): void
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $rows = [];
        foreach (['admin/laporan/harian', 'admin/laporan/mingguan'] as $link) {
            $matchRows = $this->db->table('menu_lv2')
                ->select('id, header')
                ->where('LOWER(link)', strtolower($link))
                ->get()
                ->getResultArray();
            foreach ($matchRows as $row) {
                $rows[] = $row;
            }
        }

        foreach ($rows as $row) {
            $menuId = (string) ($row['id'] ?? '');
            $headerId = (string) ($row['header'] ?? '');

            if ($menuId !== '' && $this->db->tableExists('menu_akses')) {
                $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
            }

            if ($menuId !== '') {
                $this->db->table('menu_lv2')->where('id', $menuId)->delete();
            }

            if ($headerId !== '' && $this->db->tableExists('menu_lv1')) {
                $remaining = $this->db->table('menu_lv2')->where('header', $headerId)->countAllResults();
                if ($remaining === 0) {
                    $this->db->table('menu_lv1')->where('id', $headerId)->delete();
                }
            }
        }
    }

    private function findOrCreateLv1Menu(string $label, string $icon): ?string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('ordering', 'ASC')
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            return (string) $existing['id'];
        }

        $newId = $this->generateLv1Id();
        $maxRow = $this->db->table('menu_lv1')->selectMax('ordering', 'max_ordering')->get()->getRowArray();
        $nextOrdering = ((int) ($maxRow['max_ordering'] ?? 0)) + 1;

        $this->db->table('menu_lv1')->insert([
            'id' => $newId,
            'label' => $label,
            'link' => '#',
            'icon' => $icon,
            'old_icon' => null,
            'ordering' => $nextOrdering,
        ]);

        return $newId;
    }

    private function upsertLv2ByLink(string $link, string $label, string $icon, string $header): string
    {
        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv2')->where('id', $menuId)->update([
                'label' => $label,
                'icon' => $icon,
                'header' => $header,
            ]);

            return $menuId;
        }

        $menuId = $this->generateLv2Id($header);
        $maxRow = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();
        $nextOrdering = ((int) ($maxRow['max_ordering'] ?? 0)) + 1;

        $this->db->table('menu_lv2')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'header' => $header,
            'ordering' => $nextOrdering,
        ]);

        return $menuId;
    }

    private function generateLv1Id(): string
    {
        $rows = $this->db->table('menu_lv1')->select('id')->get()->getResultArray();
        $maxSequence = 0;

        foreach ($rows as $row) {
            if (preg_match('/^(\d+)$/', (string) ($row['id'] ?? ''), $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function generateLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        $prefix = $header . '-';

        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            if (strpos($candidateId, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($candidateId, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return $header . '-' . str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function ensureAksesForMenu(string $menuId): void
    {
        if ($menuId === '') {
            return;
        }

        $groupRows = $this->db->table('menu_akses')
            ->select('group_id')
            ->distinct()
            ->get()
            ->getResultArray();

        if ($groupRows === []) {
            $groupRows = [['group_id' => 1]];
        }

        foreach ($groupRows as $row) {
            $groupId = (int) ($row['group_id'] ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $exists = $this->db->table('menu_akses')
                ->where('group_id', $groupId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isAdminGroup = $groupId === 1;
            $this->db->table('menu_akses')->insert([
                'group_id' => $groupId,
                'menu_id' => $menuId,
                'FiturAdd' => $isAdminGroup ? 1 : 0,
                'FiturEdit' => $isAdminGroup ? 1 : 0,
                'FiturDelete' => $isAdminGroup ? 1 : 0,
                'FiturExport' => $isAdminGroup ? 1 : 0,
                'FiturImport' => $isAdminGroup ? 1 : 0,
                'FiturApproval' => $isAdminGroup ? 1 : 0,
            ]);
        }
    }
}
