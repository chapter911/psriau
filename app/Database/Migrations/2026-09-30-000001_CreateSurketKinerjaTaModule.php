<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSurketKinerjaTaModule extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create trn_surket_kinerja_ta table
        if (! $db->tableExists('trn_surket_kinerja_ta')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nomor_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'tanggal_surat' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'kota_surat' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'Pekanbaru',
                ],
                'ppk_pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'ppk_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'default'    => 'Nurhidayat Nugroho, S.Ars',
                ],
                'ppk_nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => '199012212018021001',
                ],
                'ppk_jabatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'PPK Pelaksanaan Prasarana Strategis',
                ],
                'ppk_satker' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'Satuan Kerja Pelaksanaan Prasarana Strategis Riau',
                ],
                'ppk_alamat' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nama_tenaga_ahli' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'jabatan_pekerjaan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nama_badan_usaha' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'alamat_badan_usaha' => [
                    'type' => 'TEXT',
                ],
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'nama_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'lingkup_jasa' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'Manajemen Konstruksi',
                ],
                'lokasi_pekerjaan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nomor_tanggal_kontrak' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'nilai_kontrak' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'sumber_dana' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'default'    => 'APBN DIPA Satker Pelaksanaan Prasarana Strategis Riau',
                ],
                'masa_penugasan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'status_pekerjaan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'selesai 100%',
                ],
                'penilaian_keseluruhan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Sangat Baik',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('paket_id');
            $this->forge->addKey('ppk_pegawai_id');
            $this->forge->createTable('trn_surket_kinerja_ta', true);
        }

        // 2. Register Submenu in menu_lv2 under "Kontrak" (header '04')
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $kontrakHeaderId = $this->findLv1IdByLabel('Kontrak') ?? '04';

            $this->ensureLv2Menu(
                $kontrakHeaderId,
                'SURKET KINERJA TA',
                'admin/kontrak/surket-kinerja-ta',
                'fas fa-file-signature',
                3
            );
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $row = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/kontrak/surket-kinerja-ta')
                ->get()
                ->getRowArray();

            if (isset($row['id'])) {
                $menuId = (string) $row['id'];
                $db->table('menu_lv2')->where('id', $menuId)->delete();
                $this->deleteMenuAksesByMenuId($menuId);
            }
        }

        $this->forge->dropTable('trn_surket_kinerja_ta', true);
    }

    private function findLv1IdByLabel(string $label): ?string
    {
        $row = $this->db->table('menu_lv1')
            ->select('id')
            ->where('LOWER(label)', strtolower($label))
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (string) $row['id'] : null;
    }

    private function ensureLv2Menu(string $headerId, string $label, string $link, string $icon, int $ordering): void
    {
        $existingByLink = $this->findLv2ByHeaderAndLink($headerId, $link);
        if ($existingByLink !== null) {
            $this->db->table('menu_lv2')
                ->where('id', $existingByLink)
                ->update([
                    'label'    => $label,
                    'icon'     => $icon,
                    'ordering' => $ordering,
                ]);
            $this->ensureMenuAksesForMenuId($existingByLink);
            return;
        }

        $menuId = $this->generateNextLv2Id($headerId);

        $this->db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'header'   => $headerId,
            'ordering' => $ordering,
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
    }

    private function findLv2ByHeaderAndLink(string $headerId, string $link): ?string
    {
        $row = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (string) $row['id'] : null;
    }

    private function generateNextLv2Id(string $header): string
    {
        $rows = $this->db->table('menu_lv2')
            ->select('id')
            ->where('header', $header)
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            $prefix = $header . '-';
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

    private function ensureMenuAksesForMenuId(string $menuId): void
    {
        if (! $this->db->tableExists('menu_akses')) {
            return;
        }

        $roleColumn = $this->db->fieldExists('role_id', 'menu_akses') ? 'role_id' : ($this->db->fieldExists('group_id', 'menu_akses') ? 'group_id' : null);
        if ($roleColumn === null) {
            return;
        }

        $roleRows = $this->db->table('menu_akses')
            ->select($roleColumn)
            ->distinct()
            ->get()
            ->getResultArray();

        if ($roleRows === []) {
            $roleRows = [[$roleColumn => 1], [$roleColumn => 2], [$roleColumn => 3]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = (int) $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            // Role 1 (Super Admin), Role 2 (Admin), Role 3 (Editor)
            $isSuperOrAdmin = in_array($roleId, [1, 2], true);
            $isEditorOrStaff = $roleId === 3;

            $this->db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => ($isSuperOrAdmin || $isEditorOrStaff) ? 1 : 0,
                'FiturEdit'     => ($isSuperOrAdmin || $isEditorOrStaff) ? 1 : 0,
                'FiturDelete'   => ($isSuperOrAdmin || $isEditorOrStaff) ? 1 : 0,
                'FiturExport'   => 1,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturApproval' => $isSuperOrAdmin ? 1 : 0,
            ]);
        }
    }

    private function deleteMenuAksesByMenuId(string $menuId): void
    {
        if ($this->db->tableExists('menu_akses')) {
            $this->db->table('menu_akses')->where('menu_id', $menuId)->delete();
        }
    }
}
