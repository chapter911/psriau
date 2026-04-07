<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class ReplaceSidebarMenusWithMenuLevels extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('menu_lv1')) {
            $this->forge->addField([
                'id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 11,
                ],
                'label' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'link' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'icon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'old_icon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'ordering' => [
                    'type' => 'INT',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->createTable('menu_lv1');
        }

        if (! $this->db->tableExists('menu_lv2')) {
            $this->forge->addField([
                'id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'label' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'link' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'icon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'header' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'ordering' => [
                    'type' => 'INT',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->createTable('menu_lv2');
        }

        if (! $this->db->tableExists('menu_lv3')) {
            $this->forge->addField([
                'id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'label' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'icon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'link' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'header' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'ordering' => [
                    'type' => 'INT',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->createTable('menu_lv3');
        }

        if (! $this->db->tableExists('menu_akses')) {
            $this->forge->addField([
                'group_id' => [
                    'type' => 'INT',
                ],
                'menu_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'FiturAdd' => [
                    'type'       => 'BIT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => new RawSql("b'0'"),
                ],
                'FiturEdit' => [
                    'type'       => 'BIT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => new RawSql("b'0'"),
                ],
                'FiturDelete' => [
                    'type'       => 'BIT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => new RawSql("b'0'"),
                ],
                'FiturExport' => [
                    'type'       => 'BIT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => new RawSql("b'0'"),
                ],
                'FiturImport' => [
                    'type'       => 'BIT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => new RawSql("b'0'"),
                ],
                'FiturApproval' => [
                    'type'       => 'BIT',
                    'constraint' => 1,
                    'null'       => true,
                    'default'    => new RawSql("b'0'"),
                ],
            ]);

            $this->forge->addKey(['group_id', 'menu_id'], true);
            $this->forge->createTable('menu_akses');
        }

        if (! $this->db->tableExists('sidebar_menus')) {
            return;
        }

        $rows = $this->db->table('sidebar_menus')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            $this->forge->dropTable('sidebar_menus');

            return;
        }

        $rowsById = [];
        foreach ($rows as $row) {
            $rowsById[(int) $row['id']] = $row;
        }

        $depthCache = [];
        $getDepth = static function (int $id) use (&$getDepth, &$depthCache, $rowsById): int {
            if (isset($depthCache[$id])) {
                return $depthCache[$id];
            }

            if (! isset($rowsById[$id])) {
                return 0;
            }

            $parentId = $rowsById[$id]['parent_id'] !== null ? (int) $rowsById[$id]['parent_id'] : null;
            if ($parentId === null || ! isset($rowsById[$parentId])) {
                $depthCache[$id] = 0;

                return 0;
            }

            $depthCache[$id] = $getDepth($parentId) + 1;

            return $depthCache[$id];
        };

        $lv1Rows = [];
        $lv2Rows = [];
        $lv3Rows = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $depth = $getDepth($id);

            if ($depth <= 0) {
                $lv1Rows[] = $row;
                continue;
            }

            if ($depth === 1) {
                $lv2Rows[] = $row;
                continue;
            }

            $lv3Rows[] = $row;
        }

        usort($lv1Rows, static function (array $left, array $right): int {
            $leftOrdering = isset($left['sort_order']) ? (int) $left['sort_order'] : 0;
            $rightOrdering = isset($right['sort_order']) ? (int) $right['sort_order'] : 0;

            if ($leftOrdering !== $rightOrdering) {
                return $leftOrdering <=> $rightOrdering;
            }

            return ((int) $left['id']) <=> ((int) $right['id']);
        });

        usort($lv2Rows, static function (array $left, array $right) use ($rowsById): int {
            $leftParent = (int) ($left['parent_id'] ?? 0);
            $rightParent = (int) ($right['parent_id'] ?? 0);
            $leftParentOrdering = isset($rowsById[$leftParent]['sort_order']) ? (int) $rowsById[$leftParent]['sort_order'] : 0;
            $rightParentOrdering = isset($rowsById[$rightParent]['sort_order']) ? (int) $rowsById[$rightParent]['sort_order'] : 0;

            if ($leftParentOrdering !== $rightParentOrdering) {
                return $leftParentOrdering <=> $rightParentOrdering;
            }

            $leftOrdering = isset($left['sort_order']) ? (int) $left['sort_order'] : 0;
            $rightOrdering = isset($right['sort_order']) ? (int) $right['sort_order'] : 0;

            if ($leftOrdering !== $rightOrdering) {
                return $leftOrdering <=> $rightOrdering;
            }

            return ((int) $left['id']) <=> ((int) $right['id']);
        });

        usort($lv3Rows, static function (array $left, array $right) use ($rowsById): int {
            $leftParent = (int) ($left['parent_id'] ?? 0);
            $rightParent = (int) ($right['parent_id'] ?? 0);

            $leftParentParent = (int) ($rowsById[$leftParent]['parent_id'] ?? 0);
            $rightParentParent = (int) ($rowsById[$rightParent]['parent_id'] ?? 0);

            $leftRootOrdering = isset($rowsById[$leftParentParent]['sort_order']) ? (int) $rowsById[$leftParentParent]['sort_order'] : 0;
            $rightRootOrdering = isset($rowsById[$rightParentParent]['sort_order']) ? (int) $rowsById[$rightParentParent]['sort_order'] : 0;

            if ($leftRootOrdering !== $rightRootOrdering) {
                return $leftRootOrdering <=> $rightRootOrdering;
            }

            $leftParentOrdering = isset($rowsById[$leftParent]['sort_order']) ? (int) $rowsById[$leftParent]['sort_order'] : 0;
            $rightParentOrdering = isset($rowsById[$rightParent]['sort_order']) ? (int) $rowsById[$rightParent]['sort_order'] : 0;

            if ($leftParentOrdering !== $rightParentOrdering) {
                return $leftParentOrdering <=> $rightParentOrdering;
            }

            $leftOrdering = isset($left['sort_order']) ? (int) $left['sort_order'] : 0;
            $rightOrdering = isset($right['sort_order']) ? (int) $right['sort_order'] : 0;

            if ($leftOrdering !== $rightOrdering) {
                return $leftOrdering <=> $rightOrdering;
            }

            return ((int) $left['id']) <=> ((int) $right['id']);
        });

        $l1Map = [];
        $l2Map = [];
        $lv1Batch = [];
        $lv2Batch = [];
        $lv3Batch = [];
        $lv2SequenceByParent = [];
        $lv3SequenceByParent = [];

        foreach ($lv1Rows as $row) {
            $oldId = (int) $row['id'];
            $newId = str_pad((string) (count($l1Map) + 1), 2, '0', STR_PAD_LEFT);
            $l1Map[$oldId] = $newId;

            $lv1Batch[] = [
                'id'       => $newId,
                'label'    => (string) $row['label'],
                'link'     => ! empty($row['url']) ? ltrim((string) $row['url'], '/') : null,
                'icon'     => (string) ($row['icon'] ?? 'fas fa-circle'),
                'old_icon' => null,
                'ordering' => isset($row['sort_order']) ? (int) $row['sort_order'] : null,
            ];
        }

        foreach ($lv2Rows as $row) {
            $oldId = (int) $row['id'];
            $parentId = (int) ($row['parent_id'] ?? 0);
            if (! isset($l1Map[$parentId])) {
                continue;
            }

            $parentNewId = $l1Map[$parentId];
            $lv2SequenceByParent[$parentNewId] = ($lv2SequenceByParent[$parentNewId] ?? 0) + 1;
            $newId = $parentNewId . '-' . str_pad((string) $lv2SequenceByParent[$parentNewId], 2, '0', STR_PAD_LEFT);
            $l2Map[$oldId] = $newId;

            $lv2Batch[] = [
                'id'       => $newId,
                'label'    => (string) $row['label'],
                'link'     => ! empty($row['url']) ? ltrim((string) $row['url'], '/') : null,
                'icon'     => (string) ($row['icon'] ?? 'fas fa-circle'),
                'header'   => $parentNewId,
                'ordering' => isset($row['sort_order']) ? (int) $row['sort_order'] : null,
            ];
        }

        foreach ($lv3Rows as $row) {
            $oldId = (int) $row['id'];
            $parentId = (int) ($row['parent_id'] ?? 0);
            if (! isset($l2Map[$parentId])) {
                continue;
            }

            $parentNewId = $l2Map[$parentId];
            $lv3SequenceByParent[$parentNewId] = ($lv3SequenceByParent[$parentNewId] ?? 0) + 1;
            $newId = $parentNewId . '-' . str_pad((string) $lv3SequenceByParent[$parentNewId], 2, '0', STR_PAD_LEFT);

            $lv3Batch[] = [
                'id'       => $newId,
                'label'    => (string) $row['label'],
                'icon'     => (string) ($row['icon'] ?? 'fas fa-circle'),
                'link'     => ! empty($row['url']) ? ltrim((string) $row['url'], '/') : null,
                'header'   => $parentNewId,
                'ordering' => isset($row['sort_order']) ? (int) $row['sort_order'] : null,
            ];
        }

        if ($lv1Batch !== []) {
            $this->db->table('menu_lv1')->insertBatch($lv1Batch);
        }

        if ($lv2Batch !== []) {
            $this->db->table('menu_lv2')->insertBatch($lv2Batch);
        }

        if ($lv3Batch !== []) {
            $this->db->table('menu_lv3')->insertBatch($lv3Batch);
        }

        $allMenuIds = array_column($lv1Batch, 'id');
        $allMenuIds = array_merge($allMenuIds, array_column($lv2Batch, 'id'), array_column($lv3Batch, 'id'));
        $allMenuIds = array_values(array_unique($allMenuIds));

        if ($allMenuIds !== []) {
            $menuAksesRows = [];
            foreach ($allMenuIds as $menuId) {
                $menuAksesRows[] = [
                    'group_id'      => 1,
                    'menu_id'       => $menuId,
                    'FiturAdd'      => 1,
                    'FiturEdit'     => 1,
                    'FiturDelete'   => 1,
                    'FiturExport'   => 1,
                    'FiturImport'   => 1,
                    'FiturApproval' => 1,
                ];
            }
            $this->db->table('menu_akses')->insertBatch($menuAksesRows);
        }

        $this->forge->dropTable('sidebar_menus');
    }

    public function down()
    {
        if ($this->db->tableExists('sidebar_menus')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'parent_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'active_pattern' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('parent_id');
        $this->forge->createTable('sidebar_menus');

        if ($this->db->tableExists('menu_akses')) {
            $this->forge->dropTable('menu_akses');
        }

        if ($this->db->tableExists('menu_lv3')) {
            $this->forge->dropTable('menu_lv3');
        }

        if ($this->db->tableExists('menu_lv2')) {
            $this->forge->dropTable('menu_lv2');
        }

        if ($this->db->tableExists('menu_lv1')) {
            $this->forge->dropTable('menu_lv1');
        }
    }
}
