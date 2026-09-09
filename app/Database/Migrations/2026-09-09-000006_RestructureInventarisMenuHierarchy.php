<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RestructureInventarisMenuHierarchy extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv1') || ! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_lv3') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // 1. Pastikan menu induk Lv2 "Inventaris Satker" (id: 11-01) link-nya dikosongkan agar menjadi parent tree dropdown
        $db->table('menu_lv2')->where('id', '11-01')->update([
            'label'    => 'Inventaris Satker',
            'link'     => null,
            'icon'     => 'fas fa-building',
            'ordering' => 1,
        ]);

        // 2. Hapus menu_lv2 11-03 ("Daftar Barang Ruangan (DBR)") karena dipindahkan ke Lv3
        $db->table('menu_lv2')->where('id', '11-03')->delete();
        $db->table('menu_akses')->where('menu_id', '11-03')->delete();

        // 3. Update ordering "Inventaris Sekolah" di menu_lv2 menjadi ordering 2
        $db->table('menu_lv2')->where('id', '11-02')->update([
            'ordering' => 2,
        ]);

        // 4. Masukkan ke menu_lv3:
        //    Submenu 1: "Daftar Barang Inventaris" (admin/inventaris/satker)
        //    Submenu 2: "Daftar Ruangan" (admin/inventaris/dbr)
        $this->ensureLv3Menu('11-01-01', '11-01', 'Daftar Barang Inventaris', 'admin/inventaris/satker', 'far fa-circle', 1);
        $this->ensureLv3Menu('11-01-02', '11-01', 'Daftar Ruangan', 'admin/inventaris/dbr', 'far fa-circle', 2);
    }

    public function down()
    {
        $db = $this->db;

        if (! $db->tableExists('menu_lv2') || ! $db->tableExists('menu_lv3') || ! $db->tableExists('menu_akses')) {
            return;
        }

        // Hapus Lv3
        $db->table('menu_lv3')->whereIn('id', ['11-01-01', '11-01-02'])->delete();
        $db->table('menu_akses')->whereIn('menu_id', ['11-01-01', '11-01-02'])->delete();

        // Kembalikan 11-01 memiliki link
        $db->table('menu_lv2')->where('id', '11-01')->update([
            'link'     => 'admin/inventaris/satker',
            'ordering' => 1,
        ]);

        // Kembalikan 11-03 di menu_lv2
        $exists1103 = (int) $db->table('menu_lv2')->where('id', '11-03')->countAllResults();
        if ($exists1103 === 0) {
            $db->table('menu_lv2')->insert([
                'id'       => '11-03',
                'label'    => 'Daftar Barang Ruangan (DBR)',
                'link'     => 'admin/inventaris/dbr',
                'icon'     => 'fas fa-door-open',
                'header'   => '11',
                'ordering' => 2,
            ]);
            $this->ensureMenuAksesForMenuId('11-03');
        }

        // Kembalikan ordering sekolah ke 3
        $db->table('menu_lv2')->where('id', '11-02')->update([
            'ordering' => 3,
        ]);
    }

    private function ensureLv3Menu(string $id, string $headerId, string $label, string $link, string $icon, int $ordering): void
    {
        $db = $this->db;

        $existing = $db->table('menu_lv3')
            ->where('id', $id)
            ->orWhere('link', $link)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('menu_lv3')->where('id', $existing['id'])->update([
                'id'       => $id,
                'header'   => $headerId,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
        } else {
            $db->table('menu_lv3')->insert([
                'id'       => $id,
                'label'    => $label,
                'link'     => $link,
                'icon'     => $icon,
                'header'   => $headerId,
                'ordering' => $ordering,
            ]);
        }

        $this->ensureMenuAksesForMenuId($id);
    }

    private function ensureMenuAksesForMenuId(string $menuId): void
    {
        $db = $this->db;

        if (! $db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $db->fieldExists('role_id', 'menu_akses') ? 'role_id' : 'group_id';

        $roleRows = $db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if (empty($roleRows)) {
            $roleRows = [[$roleColumn => 1], [$roleColumn => 2]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = (int) $db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isSuperOrAdmin = in_array($roleId, [1, 2], true);

            $db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => $isSuperOrAdmin ? 1 : 0,
                'FiturEdit'     => $isSuperOrAdmin ? 1 : 0,
                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                'FiturExport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturApproval' => $isSuperOrAdmin ? 1 : 0,
            ]);
        }
    }
}
