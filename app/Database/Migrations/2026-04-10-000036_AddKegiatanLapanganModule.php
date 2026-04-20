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

        // Determine which column to use (role_id or group_id)
        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $groupRows = $this->db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($groupRows === []) {
            $groupRows = [[$roleColumn => 1]];
        }

        foreach ($groupRows as $row) {
            $groupId = (int) ($row[$roleColumn] ?? 0);
            if ($groupId <= 0) {
                continue;
            }

            $existing = $this->db->table('menu_akses')
                ->where('menu_id', $menuId)
                ->where($roleColumn, $groupId)
                ->countAllResults();

            if ($existing === 0) {
                $this->db->table('menu_akses')->insert([
                    'menu_id' => $menuId,
                    $roleColumn => $groupId,
                ]);
            }
        }
    }
}
