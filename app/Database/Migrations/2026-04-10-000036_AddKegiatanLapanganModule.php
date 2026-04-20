<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKegiatanLapanganModule extends Migration
{
    public function up()
    {
        $this->createTables();
        $this->seedMenus();
    }

    public function down()
    {
        $this->removeMenus();

        if ($this->db->tableExists('kegiatan_lapangan_photos')) {
            $this->forge->dropTable('kegiatan_lapangan_photos', true);
        }

        if ($this->db->tableExists('kegiatan_lapangan')) {
            $this->forge->dropTable('kegiatan_lapangan', true);
        }
    }

    private function createTables(): void
    {
        if (! $this->db->tableExists('kegiatan_lapangan')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                ],
                'activity_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'location' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                ],
                'created_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                ],
                'created_by_user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
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
            $this->forge->addKey('activity_date');
            $this->forge->createTable('kegiatan_lapangan');
        }

        if (! $this->db->tableExists('kegiatan_lapangan_photos')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'activity_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'photo_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'photo_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'sort_order' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
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
            $this->forge->addKey('activity_id');
            $this->forge->addKey('sort_order');
            $this->forge->createTable('kegiatan_lapangan_photos');
        }
    }

    private function seedMenus(): void
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $parentId = $this->upsertLv1Menu('Dokumentasi', 'fas fa-images');
        $childId = $this->upsertLv2Menu($parentId, 'Kegiatan Lapangan', 'admin/dokumentasi/kegiatan-lapangan', 'far fa-camera-retro');

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAccess($parentId);
            $this->ensureAccess($childId);
        }
    }

    private function removeMenus(): void
    {
        if (! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $rows = $this->db->table('menu_lv2')
            ->select('id, header')
            ->where('LOWER(link)', 'admin/dokumentasi/kegiatan-lapangan')
            ->get()
            ->getResultArray();

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
                    if ($this->db->tableExists('menu_akses')) {
                        $this->db->table('menu_akses')->where('menu_id', $headerId)->delete();
                    }

                    $this->db->table('menu_lv1')->where('id', $headerId)->delete();
                }
            }
        }
    }

    private function upsertLv1Menu(string $label, string $icon): string
    {
        $existing = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->get()
            ->getRowArray();

        if (is_array($existing) && ! empty($existing['id'])) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv1')->where('id', $menuId)->update([
                'label' => $label,
                'icon' => $icon,
                'link' => '#',
            ]);

            return $menuId;
        }

        $menuId = $this->generateLv1Id();
        $nextOrdering = $this->nextLv1Ordering();

        $this->db->table('menu_lv1')->insert([
            'id' => $menuId,
            'label' => $label,
            'link' => '#',
            'icon' => $icon,
            'old_icon' => null,
            'ordering' => $nextOrdering,
        ]);

        return $menuId;
    }

    private function upsertLv2Menu(string $header, string $label, string $link, string $icon): string
    {
        $existing = $this->db->table('menu_lv2')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existing) && ! empty($existing['id'])) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv2')->where('id', $menuId)->update([
                'label' => $label,
                'icon' => $icon,
                'header' => $header,
            ]);

            return $menuId;
        }

        $menuId = $this->generateLv2Id($header);
        $nextOrdering = $this->nextLv2Ordering($header);

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

    private function ensureAccess(string $menuId): void
    {
        if ($menuId === '' || ! $this->db->tableExists('menu_akses')) {
            return;
        }

        $fields = $this->menuAksesFields();
        $hasRoleId = in_array('role_id', $fields, true);
        $hasGroupId = in_array('group_id', $fields, true);
        $roleColumn = $hasRoleId ? 'role_id' : ($hasGroupId ? 'group_id' : '');
        if ($roleColumn === '') {
            return;
        }

        $roleRows = $this->db->table('menu_akses')->select($roleColumn)->distinct()->get()->getResultArray();

        if ($roleRows === []) {
            $roleRows = [[$roleColumn => 1]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $existsBuilder = $this->db->table('menu_akses')->where('menu_id', $menuId);
            $existsBuilder->groupStart()->where($roleColumn, $roleId);
            if ($roleColumn === 'role_id' && $hasGroupId) {
                $existsBuilder->orWhere('group_id', $roleId);
            }
            if ($roleColumn === 'group_id' && $hasRoleId) {
                $existsBuilder->orWhere('role_id', $roleId);
            }
            $existsBuilder->groupEnd();

            $exists = (int) $existsBuilder->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isPrivileged = $roleId === 1 || $this->isSuperAdministratorRole($roleId);
            $value = $isPrivileged ? 1 : 0;

            $insertData = [
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => $value,
                'FiturEdit' => $value,
                'FiturDelete' => $value,
                'FiturExport' => $value,
                'FiturImport' => $value,
                'FiturApproval' => $value,
            ];

            if ($hasRoleId) {
                $insertData['role_id'] = $roleId;
            }
            if ($hasGroupId) {
                $insertData['group_id'] = $roleId;
            }

            $this->db->table('menu_akses')->insert($insertData);
        }
    }

    private function menuAksesFields(): array
    {
        if (! $this->db->tableExists('menu_akses')) {
            return [];
        }

        $fields = [];
        $result = $this->db->query('SHOW COLUMNS FROM menu_akses')->getResultArray();
        foreach ($result as $row) {
            $name = strtolower((string) ($row['Field'] ?? ''));
            if ($name !== '') {
                $fields[] = $name;
            }
        }

        return $fields;
    }

    private function nextLv1Ordering(): int
    {
        $row = $this->db->table('menu_lv1')->selectMax('ordering', 'max_ordering')->get()->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }

    private function nextLv2Ordering(string $header): int
    {
        $row = $this->db->table('menu_lv2')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }

    private function isSuperAdministratorRole(int $roleId): bool
    {
        if (! $this->db->tableExists('access_roles')) {
            return false;
        }

        $row = $this->db->table('access_roles')
            ->select('role_key')
            ->where('id', $roleId)
            ->get()
            ->getRowArray();

        $roleKey = strtolower(trim((string) ($row['role_key'] ?? '')));

        return in_array($roleKey, ['super administrator', 'super_administrator', 'super-admin', 'superadmin'], true);
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
}