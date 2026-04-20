<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuditHistoriesAndMenu extends Migration
{
    public function up()
    {
        $this->createAuditTable();
        $this->createHistoryMenus();
    }

    public function down()
    {
        if ($this->db->tableExists('menu_lv2')) {
            foreach (['admin/history/login', 'admin/history/edit', 'admin/history/delete'] as $link) {
                $row = $this->db->table('menu_lv2')->select('id, header')->where('LOWER(link)', strtolower($link))->get()->getRowArray();
                if (! is_array($row)) {
                    continue;
                }

                $menuId = (string) ($row['id'] ?? '');
                $header = (string) ($row['header'] ?? '');
                if ($menuId !== '' && $this->db->tableExists('menu_akses')) {
                    $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
                }

                if ($menuId !== '') {
                    $this->db->table('menu_lv2')->where('id', $menuId)->delete();
                }

                if ($header !== '') {
                    $remaining = $this->db->table('menu_lv2')->where('header', $header)->countAllResults();
                    if ($remaining === 0) {
                        if ($this->db->tableExists('menu_akses')) {
                            $this->db->table('menu_akses')->where('menu_id', $header)->delete();
                        }
                        $this->db->table('menu_lv1')->where('id', $header)->delete();
                    }
                }
            }
        }

        if ($this->db->tableExists('audit_histories')) {
            $this->forge->dropTable('audit_histories', true);
        }
    }

    private function createAuditTable(): void
    {
        if ($this->db->tableExists('audit_histories')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'action_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'module_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'table_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'record_id' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'request_data_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'before_data_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'after_data_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'happened_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('action_type');
        $this->forge->addKey('happened_at');
        $this->forge->addKey(['table_name', 'record_id']);
        $this->forge->createTable('audit_histories', true);
    }

    private function createHistoryMenus(): void
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $historyLv1Id = $this->findOrCreateLv1('History', 'fas fa-history');
        if ($historyLv1Id === null) {
            return;
        }

        $menuIds = [];
        $menuIds[] = $this->upsertLv2('admin/history/login', 'Login', 'far fa-circle', $historyLv1Id, 1);
        $menuIds[] = $this->upsertLv2('admin/history/edit', 'Edit', 'far fa-circle', $historyLv1Id, 2);
        $menuIds[] = $this->upsertLv2('admin/history/delete', 'Delete', 'far fa-circle', $historyLv1Id, 3);

        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        $this->ensureMenuAccessRow($historyLv1Id);
        foreach ($menuIds as $menuId) {
            if ($menuId !== '') {
                $this->ensureMenuAccessRow($menuId);
            }
        }
    }

    private function findOrCreateLv1(string $label, string $icon): ?string
    {
        $existing = $this->db->table('menu_lv1')->select('id')->where('LOWER(label)', strtolower($label))->orderBy('ordering', 'ASC')->get()->getRowArray();
        if (is_array($existing)) {
            return (string) $existing['id'];
        }

        $newId = $this->nextLv1Id();
        if ($newId === null) {
            return null;
        }

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

    private function upsertLv2(string $link, string $label, string $icon, string $header, int $ordering): string
    {
        $existing = $this->db->table('menu_lv2')->select('id')->where('LOWER(link)', strtolower($link))->get()->getRowArray();
        if (is_array($existing)) {
            $id = (string) $existing['id'];
            $this->db->table('menu_lv2')->where('id', $id)->update([
                'label' => $label,
                'icon' => $icon,
                'header' => $header,
                'ordering' => $ordering,
            ]);
            return $id;
        }

        $id = $this->nextLv2Id($header);
        $this->db->table('menu_lv2')->insert([
            'id' => $id,
            'label' => $label,
            'link' => $link,
            'icon' => $icon,
            'header' => $header,
            'ordering' => $ordering,
        ]);

        return $id;
    }

    private function ensureMenuAccessRow(string $menuId): void
    {
        $roleColumn = $this->resolveMenuAccessRoleColumn();
        if ($roleColumn === null) {
            return;
        }

        $roleIds = [];
        if ($this->db->tableExists('access_roles')) {
            $rows = $this->db->table('access_roles')->select('id')->where('is_active', 1)->get()->getResultArray();
            $roleIds = array_values(array_filter(array_map(static fn (array $row): int => (int) ($row['id'] ?? 0), $rows), static fn (int $id): bool => $id > 0));
        }

        if ($roleIds === []) {
            $rows = $this->db->table('menu_akses')->select($roleColumn)->distinct()->get()->getResultArray();
            $roleIds = array_values(array_filter(array_map(static fn (array $row): int => (int) ($row[$roleColumn] ?? 0), $rows), static fn (int $id): bool => $id > 0));
        }

        if ($roleIds === []) {
            $roleIds = [1];
        }

        foreach ($roleIds as $roleId) {
            $exists = (int) $this->db->table('menu_akses')->where($roleColumn, $roleId)->where('menu_id', $menuId)->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $this->db->table('menu_akses')->insert([
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => 0,
                'FiturEdit' => 0,
                'FiturDelete' => 0,
                'FiturExport' => 0,
                'FiturImport' => 0,
                'FiturApproval' => 0,
            ]);
        }
    }

    private function resolveMenuAccessRoleColumn(): ?string
    {
        if (! $this->db->tableExists('menu_akses')) {
            return null;
        }

        $fields = [];
        $result = $this->db->query('SHOW COLUMNS FROM menu_akses')->getResultArray();
        foreach ($result as $row) {
            $name = strtolower((string) ($row['Field'] ?? ''));
            if ($name !== '') {
                $fields[] = $name;
            }
        }

        if (in_array('role_id', $fields, true)) {
            return 'role_id';
        }

        if (in_array('group_id', $fields, true)) {
            return 'group_id';
        }

        return null;
    }

    private function nextLv1Id(): ?string
    {
        $rows = $this->db->table('menu_lv1')->select('id')->get()->getResultArray();
        $max = 0;
        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');
            if (preg_match('/^(\d+)$/', $id, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    private function nextLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')->select('id')->where('header', $header)->get()->getResultArray();
        $max = 0;
        $prefix = $header . '-';
        foreach ($rows as $row) {
            $id = (string) ($row['id'] ?? '');
            if (strpos($id, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($id, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $header . '-' . str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }
}
