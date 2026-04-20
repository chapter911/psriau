<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMasterWilayahSekolah extends Migration
{
    public function up()
    {
        $this->createMasterTables();
        $this->seedMenus();
    }

    public function down()
    {
        $this->removeMenus();

        if ($this->db->tableExists('mst_sekolah')) {
            $this->forge->dropTable('mst_sekolah', true);
        }

        if ($this->db->tableExists('mst_kelurahan')) {
            $this->forge->dropTable('mst_kelurahan', true);
        }

        if ($this->db->tableExists('mst_kecamatan')) {
            $this->forge->dropTable('mst_kecamatan', true);
        }

        if ($this->db->tableExists('mst_kabupaten')) {
            $this->forge->dropTable('mst_kabupaten', true);
        }

        if ($this->db->tableExists('mst_provinsi')) {
            $this->forge->dropTable('mst_provinsi', true);
        }
    }

    private function createMasterTables(): void
    {
        if (! $this->db->tableExists('mst_provinsi')) {
            $this->forge->addField([
                'kode_provinsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'nama_provinsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('kode_provinsi', true);
            $this->forge->createTable('mst_provinsi', true);
        }

        if (! $this->db->tableExists('mst_kabupaten')) {
            $this->forge->addField([
                'kode_provinsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kode_kabupaten' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'nama_kabupaten' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['kode_provinsi', 'kode_kabupaten'], true);
            $this->forge->createTable('mst_kabupaten', true);
        }

        if (! $this->db->tableExists('mst_kecamatan')) {
            $this->forge->addField([
                'kode_provinsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kode_kabupaten' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kode_kecamatan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'nama_kecamatan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'kategori_konflik' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['kode_provinsi', 'kode_kabupaten', 'kode_kecamatan'], true);
            $this->forge->createTable('mst_kecamatan', true);
        }

        if (! $this->db->tableExists('mst_kelurahan')) {
            $this->forge->addField([
                'kode_provinsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kode_kabupaten' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kode_kecamatan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'kode_kelurahan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ],
                'nama_kelurahan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'kategori_konflik' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['kode_provinsi', 'kode_kabupaten', 'kode_kecamatan', 'kode_kelurahan'], true);
            $this->forge->createTable('mst_kelurahan', true);
        }

        if (! $this->db->tableExists('mst_sekolah')) {
            $this->forge->addField([
                'npsn' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                ],
                'nama' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'jenis' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'nsm' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'null' => true,
                ],
                'kabupaten' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'kecamatan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'latitude' => [
                    'type' => 'DOUBLE',
                    'null' => true,
                ],
                'longitude' => [
                    'type' => 'DOUBLE',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'updated_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('npsn', true);
            $this->forge->createTable('mst_sekolah', true);
        }
    }

    private function seedMenus(): void
    {
        if (! $this->db->tableExists('menu_lv1') || ! $this->db->tableExists('menu_lv2') || ! $this->db->tableExists('menu_lv3')) {
            return;
        }

        $masterId = $this->findOrCreateLv1Menu('Master', 'fas fa-boxes');
        if ($masterId === null) {
            return;
        }

        $sekolahId = $this->upsertLv2ByLink('admin/master/sekolah', 'Sekolah', 'far fa-school', $masterId);
        $wilayahId = $this->upsertLv2ByLink('admin/master/wilayah', 'Wilayah Administratif', 'far fa-map', $masterId);

        $provinsiId = $this->upsertLv3ByLink('admin/master/provinsi', 'Provinsi', 'far fa-circle', $wilayahId);
        $kabupatenId = $this->upsertLv3ByLink('admin/master/kabupaten', 'Kabupaten', 'far fa-circle', $wilayahId);
        $kecamatanId = $this->upsertLv3ByLink('admin/master/kecamatan', 'Kecamatan', 'far fa-circle', $wilayahId);
        $kelurahanId = $this->upsertLv3ByLink('admin/master/kelurahan', 'Kelurahan', 'far fa-circle', $wilayahId);

        if ($this->db->tableExists('menu_akses')) {
            $this->ensureAksesForMenu($masterId);
            $this->ensureAksesForMenu($sekolahId);
            $this->ensureAksesForMenu($wilayahId);
            $this->ensureAksesForMenu($provinsiId);
            $this->ensureAksesForMenu($kabupatenId);
            $this->ensureAksesForMenu($kecamatanId);
            $this->ensureAksesForMenu($kelurahanId);
        }
    }

    private function removeMenus(): void
    {
        if (! $this->db->tableExists('menu_lv3') || ! $this->db->tableExists('menu_lv2')) {
            return;
        }

        $lv3Links = [
            'admin/master/provinsi',
            'admin/master/kabupaten',
            'admin/master/kecamatan',
            'admin/master/kelurahan',
        ];

        foreach ($lv3Links as $link) {
            $row = $this->db->table('menu_lv3')
                ->select('id')
                ->where('LOWER(link)', strtolower($link))
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                continue;
            }

            $menuId = (string) ($row['id'] ?? '');
            if ($menuId === '') {
                continue;
            }

            if ($this->db->tableExists('menu_akses')) {
                $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
            }
            $this->db->table('menu_lv3')->where('id', $menuId)->delete();
        }

        $lv2Links = [
            'admin/master/sekolah',
            'admin/master/wilayah',
        ];

        foreach ($lv2Links as $link) {
            $row = $this->db->table('menu_lv2')
                ->select('id, header')
                ->where('LOWER(link)', strtolower($link))
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                continue;
            }

            $menuId = (string) ($row['id'] ?? '');
            $headerId = (string) ($row['header'] ?? '');

            if ($menuId !== '' && $this->db->tableExists('menu_akses')) {
                $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
            }

            if ($menuId !== '') {
                $this->db->table('menu_lv2')->where('id', $menuId)->delete();
            }

            if ($headerId !== '' && $this->db->tableExists('menu_lv2') && $this->db->tableExists('menu_lv1')) {
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

    private function upsertLv3ByLink(string $link, string $label, string $icon, string $header): string
    {
        $existing = $this->db->table('menu_lv3')
            ->select('id')
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if (is_array($existing)) {
            $menuId = (string) $existing['id'];
            $this->db->table('menu_lv3')->where('id', $menuId)->update([
                'label' => $label,
                'icon' => $icon,
                'header' => $header,
            ]);

            return $menuId;
        }

        $menuId = $this->generateLv3Id($header);
        $maxRow = $this->db->table('menu_lv3')
            ->selectMax('ordering', 'max_ordering')
            ->where('header', $header)
            ->get()
            ->getRowArray();
        $nextOrdering = ((int) ($maxRow['max_ordering'] ?? 0)) + 1;

        $this->db->table('menu_lv3')->insert([
            'id' => $menuId,
            'label' => $label,
            'icon' => $icon,
            'link' => $link,
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
        $rows = $this->db->table('menu_lv2')->select('id')->where('header', $header)->get()->getResultArray();
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

    private function generateLv3Id(string $header): string
    {
        $rows = $this->db->table('menu_lv3')->select('id')->where('header', $header)->get()->getResultArray();
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

        $roleColumn = $this->resolveMenuAksesRoleColumn();
        if ($roleColumn === null) {
            return;
        }

        $fields = $this->menuAksesFields();
        $hasRoleId = in_array('role_id', $fields, true);
        $hasGroupId = in_array('group_id', $fields, true);

        $roleRows = $this->db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($roleRows === []) {
            $roleRows = [[$roleColumn => 1]];
        }

        foreach ($roleRows as $row) {
            $roleId = (int) ($row[$roleColumn] ?? 0);
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

            $isAdminRole = $roleId === 1;

            $insertData = [
                $roleColumn => $roleId,
                'menu_id' => $menuId,
                'FiturAdd' => $isAdminRole ? 1 : 0,
                'FiturEdit' => $isAdminRole ? 1 : 0,
                'FiturDelete' => 0,
                'FiturExport' => $isAdminRole ? 1 : 0,
                'FiturImport' => $isAdminRole ? 1 : 0,
                'FiturApproval' => 0,
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

    private function resolveMenuAksesRoleColumn(): ?string
    {
        $fields = $this->menuAksesFields();

        if (in_array('role_id', $fields, true)) {
            return 'role_id';
        }

        if (in_array('group_id', $fields, true)) {
            return 'group_id';
        }

        return null;
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
}
