<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKompuModuleTablesAndMenus extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Table: trn_kompu_sosmed (Daftar Akun Media Sosial & Kredensial)
        if (! $db->tableExists('trn_kompu_sosmed')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'nama_sosmed' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'kategori' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Media Sosial',
                ],
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'email_login' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'url_profil' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'metode_login' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'icon' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'fas fa-hashtag',
                ],
                'ordering' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('ordering');
            $this->forge->addKey('is_active');
            $this->forge->createTable('trn_kompu_sosmed', true);

            // Seed initial data from "Daftar Akun Sosmed Satker PPS Riau.docx"
            $now = date('Y-m-d H:i:s');
            $seedSosmed = [
                [
                    'nama_sosmed'  => 'Email Satker',
                    'kategori'     => 'Email',
                    'username'     => 'satkerpsriau@gmail.com',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => '',
                    'url_profil'   => '',
                    'metode_login' => 'Akun Utama Google Satker',
                    'keterangan'   => 'Akun email resmi satuan kerja untuk integrasi layanan Google & medsos.',
                    'icon'         => 'fas fa-envelope',
                    'ordering'     => 1,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
                [
                    'nama_sosmed'  => 'Instagram',
                    'kategori'     => 'Media Sosial',
                    'username'     => 'pu_prasaranastrategis_riau',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => 'psriau806',
                    'url_profil'   => 'https://www.instagram.com/pu_prasaranastrategis_riau/',
                    'metode_login' => 'Username & Password',
                    'keterangan'   => 'Kanal publikasi foto, reels, dan informasi kegiatan strategis PUPR Riau.',
                    'icon'         => 'fab fa-instagram',
                    'ordering'     => 2,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
                [
                    'nama_sosmed'  => 'Threads',
                    'kategori'     => 'Media Sosial',
                    'username'     => 'pu_prasaranastrategis_riau',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => 'psriau806',
                    'url_profil'   => 'https://www.threads.com/@pu_prasaranastrategis_riau',
                    'metode_login' => 'Terhubung ke Akun Instagram',
                    'keterangan'   => 'Kanal microblogging resmi terhubung ke akun Instagram PPS Riau.',
                    'icon'         => 'fab fa-threads',
                    'ordering'     => 3,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
                [
                    'nama_sosmed'  => 'TikTok',
                    'kategori'     => 'Media Sosial',
                    'username'     => '@puppsriau',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => '',
                    'url_profil'   => 'https://www.tiktok.com/@puppsriau',
                    'metode_login' => 'Login dengan Google / Gmail',
                    'keterangan'   => 'Publikasi video pendek kegiatan lapangan dan edukasi infrastruktur.',
                    'icon'         => 'fab fa-tiktok',
                    'ordering'     => 4,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
                [
                    'nama_sosmed'  => 'X (Twitter)',
                    'kategori'     => 'Media Sosial',
                    'username'     => '@puppsriau',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => '608SatkerppsriauX',
                    'url_profil'   => 'https://x.com/puppsriau',
                    'metode_login' => 'Username & Password',
                    'keterangan'   => 'Kanal komunikasi cepat, siaran pers, dan informasi publik PUPR Riau.',
                    'icon'         => 'fab fa-x-twitter',
                    'ordering'     => 5,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
                [
                    'nama_sosmed'  => 'Facebook',
                    'kategori'     => 'Media Sosial',
                    'username'     => 'satkerpsriau@gmail.com',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => '24434Satkerpsriau',
                    'url_profil'   => 'https://www.facebook.com/profile.php?id=61594954350241&sk=about',
                    'metode_login' => 'Email & Password',
                    'keterangan'   => 'Halaman Facebook resmi Satker Prasarana Strategis Riau.',
                    'icon'         => 'fab fa-facebook-f',
                    'ordering'     => 6,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
                [
                    'nama_sosmed'  => 'YouTube',
                    'kategori'     => 'Media Sosial',
                    'username'     => '@SatkerPPSRiau',
                    'email_login'  => 'satkerpsriau@gmail.com',
                    'password'     => '',
                    'url_profil'   => 'https://www.youtube.com/@SatkerPPSRiau',
                    'metode_login' => 'Login dengan Google / Gmail',
                    'keterangan'   => 'Kanal video dokumentasi proyek dan laporan visual pembangunan.',
                    'icon'         => 'fab fa-youtube',
                    'ordering'     => 7,
                    'is_active'    => 1,
                    'created_by'   => 'System',
                    'created_at'   => $now,
                ],
            ];

            $db->table('trn_kompu_sosmed')->insertBatch($seedSosmed);
        }

        // 2. Table: trn_kompu_log_pengiriman (Audit Log Pengiriman Password ke Email)
        if (! $db->tableExists('trn_kompu_log_pengiriman')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'sosmed_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'nama_sosmed' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'user_nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'user_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'email_tujuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'sukses',
                ],
                'pesan_status' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => true,
                ],
                'user_agent' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('sosmed_id');
            $this->forge->addKey('user_id');
            $this->forge->addKey('status');
            $this->forge->createTable('trn_kompu_log_pengiriman', true);
        }

        // 3. Table: cfg_kompu_user_access (Otorisasi Akses User Khusus KOMPU)
        if (! $db->tableExists('cfg_kompu_user_access')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'can_access' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'can_view_log' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'notes' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'created_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
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
            $this->forge->addUniqueKey('user_id', 'uk_kompu_user');
            $this->forge->createTable('cfg_kompu_user_access', true);

            // Seed Super Admin and Admin users as default authorized users
            if ($db->tableExists('users')) {
                $initialUsers = $db->table('users')
                    ->select('id, role')
                    ->whereIn('role', ['super_administrator', 'admin'])
                    ->get()
                    ->getResultArray();

                $seedAccess = [];
                $now = date('Y-m-d H:i:s');
                foreach ($initialUsers as $u) {
                    $seedAccess[] = [
                        'user_id'      => (int) $u['id'],
                        'can_access'   => 1,
                        'can_view_log' => $u['role'] === 'super_administrator' ? 1 : 0,
                        'notes'        => 'Default Administrator',
                        'created_by'   => 'System',
                        'created_at'   => $now,
                    ];
                }
                if (! empty($seedAccess)) {
                    $db->table('cfg_kompu_user_access')->insertBatch($seedAccess);
                }
            }
        }

        // 4. Menu Registration: Menu Level 1 "KOMPU" & Submenus
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $kompuLv1Id = $this->findOrCreateLv1Menu('KOMPU', null, 'fas fa-bullhorn', $this->getNextLv1Ordering());

            if ($kompuLv1Id !== null) {
                // Submenu 1: Media Sosial Satker
                $this->ensureLv2Menu(
                    $kompuLv1Id,
                    'Media Sosial Satker',
                    'admin/kompu/sosmed',
                    'fas fa-hashtag',
                    1
                );

                // Submenu 2: Log Pengiriman Kredensial
                $this->ensureLv2Menu(
                    $kompuLv1Id,
                    'Log Pengiriman Kredensial',
                    'admin/kompu/log-kredensial',
                    'fas fa-envelope-open-text',
                    2
                );

                // Submenu 3: Pengaturan (Strict Super Administrator Only)
                $this->ensureLv2Menu(
                    $kompuLv1Id,
                    'Pengaturan',
                    'admin/kompu/pengaturan',
                    'fas fa-user-shield',
                    3,
                    true // isSuperAdminOnly
                );
            }
        }
    }

    public function down()
    {
        $db = $this->db;

        // Clean up menus
        $kompuId = $this->findLv1IdByLabel('KOMPU');
        if ($kompuId !== null && $db->tableExists('menu_lv2')) {
            $submenus = $db->table('menu_lv2')->where('header', $kompuId)->get()->getResultArray();
            foreach ($submenus as $sm) {
                $this->deleteMenuAksesByMenuId((string) $sm['id']);
                $db->table('menu_lv2')->where('id', $sm['id'])->delete();
            }
            $this->deleteMenuAksesByMenuId($kompuId);
            $db->table('menu_lv1')->where('id', $kompuId)->delete();
        }

        // Drop tables
        $this->forge->dropTable('cfg_kompu_user_access', true);
        $this->forge->dropTable('trn_kompu_log_pengiriman', true);
        $this->forge->dropTable('trn_kompu_sosmed', true);
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

    private function findOrCreateLv1Menu(string $label, ?string $link, string $icon, int $ordering): ?string
    {
        $existingId = $this->findLv1IdByLabel($label);
        if ($existingId !== null) {
            return $existingId;
        }

        $menuId = $this->generateNextLv1Id();

        $this->db->table('menu_lv1')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'ordering' => $ordering,
        ]);

        $this->ensureMenuAksesForMenuId($menuId, false);

        return $menuId;
    }

    private function generateNextLv1Id(): string
    {
        $rows = $this->db->table('menu_lv1')
            ->select('id')
            ->get()
            ->getResultArray();

        $maxSequence = 0;
        foreach ($rows as $row) {
            $candidateId = (string) ($row['id'] ?? '');
            if (preg_match('/^(\d+)$/', $candidateId, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return str_pad((string) ($maxSequence + 1), 2, '0', STR_PAD_LEFT);
    }

    private function getNextLv1Ordering(): int
    {
        $row = $this->db->table('menu_lv1')
            ->selectMax('ordering', 'max_ordering')
            ->get()
            ->getRowArray();

        return ((int) ($row['max_ordering'] ?? 0)) + 1;
    }

    private function ensureLv2Menu(string $headerId, string $label, string $link, string $icon, int $ordering, bool $isSuperAdminOnly = false): void
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
            $this->ensureMenuAksesForMenuId($existingByLink, $isSuperAdminOnly);
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

        $this->ensureMenuAksesForMenuId($menuId, $isSuperAdminOnly);
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

    private function ensureMenuAksesForMenuId(string $menuId, bool $isSuperAdminOnly = false): void
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
            $roleRows = [[$roleColumn => 1], [$roleColumn => 2]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow[$roleColumn] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            // If this submenu is strictly for Super Administrator, skip other roles!
            if ($isSuperAdminOnly && $roleId !== 1) {
                // Remove if accidentally exists
                $this->db->table('menu_akses')
                    ->where($roleColumn, $roleId)
                    ->where('menu_id', $menuId)
                    ->delete();
                continue;
            }

            $exists = (int) $this->db->table('menu_akses')
                ->where($roleColumn, $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isSuperAdmin = ($roleId === 1);
            $isAdmin      = ($roleId === 2);

            $this->db->table('menu_akses')->insert([
                $roleColumn     => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => ($isSuperAdmin || $isAdmin) ? 1 : 0,
                'FiturEdit'     => ($isSuperAdmin || $isAdmin) ? 1 : 0,
                'FiturDelete'   => $isSuperAdmin ? 1 : 0,
                'FiturExport'   => ($isSuperAdmin || $isAdmin) ? 1 : 0,
                'FiturImport'   => $isSuperAdmin ? 1 : 0,
                'FiturApproval' => 0,
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
