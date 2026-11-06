<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSwakelolaPerjadinModule extends Migration
{
    public function up()
    {
        $db = $this->db;

        // 1. Create table trn_swakelola_perjadin_personel
        if (! $db->tableExists('trn_swakelola_perjadin_personel')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'paket_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'realisasi_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'kode_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'nama_paket' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'kode_akun' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'nomor_spt' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'tanggal_spt' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'nomor_spd' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'nama_personel' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'nip' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'jabatan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'instansi' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'default'    => 'Satker Pelaksanaan Prasarana Strategis Riau',
                ],
                'golongan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'pegawai_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'is_matched_master' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'kota_tujuan' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'tanggal_berangkat' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'tanggal_kembali' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'lama_hari' => [
                    'type'       => 'INT',
                    'constraint' => 3,
                    'default'    => 1,
                ],
                'maksud_tugas' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nomor_spm' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'tanggal_spm' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'nomor_sp2d' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'nilai_biaya' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '15,2',
                    'default'    => 0.00,
                ],
                'file_bukti' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
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
            $this->forge->addKey('paket_id');
            $this->forge->addKey('realisasi_id');
            $this->forge->addKey('pegawai_id');
            $this->forge->addKey('nomor_spt');
            $this->forge->addKey('nama_personel');
            $this->forge->createTable('trn_swakelola_perjadin_personel', true);
        }

        // 2. Register Submenu in menu_lv2 under 'Swakelola'
        if ($db->tableExists('menu_lv1') && $db->tableExists('menu_lv2')) {
            $swakelolaRow = $db->table('menu_lv1')
                ->select('id')
                ->where('LOWER(label)', 'swakelola')
                ->get()
                ->getRowArray();

            $swakelolaId = isset($swakelolaRow['id']) ? (string) $swakelolaRow['id'] : null;

            if ($swakelolaId !== null) {
                $this->ensureLv2Menu(
                    $swakelolaId,
                    'Daftar Perjadin',
                    'admin/swakelola/perjadin',
                    'fas fa-plane-departure',
                    5
                );
            }
        }

        // 3. Populate and match Perjadin Personel records
        $this->seedAllPerjadinPersonel();
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('menu_lv2')) {
            $menuRow = $db->table('menu_lv2')
                ->select('id')
                ->where('LOWER(link)', 'admin/swakelola/perjadin')
                ->get()
                ->getRowArray();

            if ($menuRow && isset($menuRow['id'])) {
                $menuId = (string) $menuRow['id'];
                $db->table('menu_lv2')->where('id', $menuId)->delete();
                if ($db->tableExists('menu_akses')) {
                    $db->table('menu_akses')->where('menu_id', $menuId)->delete();
                }
            }
        }

        $this->forge->dropTable('trn_swakelola_perjadin_personel', true);
    }

    private function ensureLv2Menu(string $headerId, string $label, string $link, string $icon, int $ordering): void
    {
        $db = $this->db;

        $existing = $db->table('menu_lv2')
            ->select('id')
            ->where('header', $headerId)
            ->where('LOWER(link)', strtolower($link))
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            $menuId = (string) $existing['id'];
            $db->table('menu_lv2')->where('id', $menuId)->update([
                'label'    => $label,
                'icon'     => $icon,
                'ordering' => $ordering,
            ]);
            $this->ensureMenuAksesForMenuId($menuId);
            return;
        }

        $menuId = $this->generateNextLv2Id($headerId);

        $db->table('menu_lv2')->insert([
            'id'       => $menuId,
            'label'    => $label,
            'link'     => $link,
            'icon'     => $icon,
            'header'   => $headerId,
            'ordering' => $ordering,
        ]);

        $this->ensureMenuAksesForMenuId($menuId);
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

        $roleRows = $this->db->table('menu_akses')
            ->select('role_id')
            ->distinct()
            ->get()
            ->getResultArray();

        if ($roleRows === []) {
            $roleRows = [['role_id' => 1], ['role_id' => 2], ['role_id' => 3], ['role_id' => 5], ['role_id' => 6]];
        }

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow['role_id'] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = (int) $this->db->table('menu_akses')
                ->where('role_id', $roleId)
                ->where('menu_id', $menuId)
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $isSuperOrAdmin = in_array($roleId, [1, 2], true);
            $isKeuangan     = ($roleId === 5);
            $isTataUsaha    = in_array($roleId, [3, 6], true);

            $this->db->table('menu_akses')->insert([
                'role_id'       => $roleId,
                'menu_id'       => $menuId,
                'FiturAdd'      => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                'FiturEdit'     => ($isSuperOrAdmin || $isTataUsaha || $isKeuangan) ? 1 : 0,
                'FiturDelete'   => $isSuperOrAdmin ? 1 : 0,
                'FiturExport'   => 1,
                'FiturImport'   => $isSuperOrAdmin ? 1 : 0,
                'FiturApproval' => ($isSuperOrAdmin || $isKeuangan) ? 1 : 0,
            ]);
        }
    }

    /**
     * Helper to match person against mst_pegawai by NIP, NRP, or Name
     */
    private function matchWithMasterPegawai(string $name, ?string $nip): array
    {
        $db = $this->db;
        $res = [
            'pegawai_id'        => null,
            'is_matched_master' => 0,
            'official_name'     => $name,
            'official_nip'      => $nip,
            'official_golongan' => null,
            'official_jabatan'  => null,
        ];

        if (! $db->tableExists('mst_pegawai')) {
            return $res;
        }

        $cleanNip = ! empty($nip) ? preg_replace('/[^0-9]/', '', $nip) : '';

        // 1. Match by NIP if valid and length >= 8
        if (strlen($cleanNip) >= 8) {
            $row = $db->table('mst_pegawai')
                ->select('mst_pegawai.id, mst_pegawai.nama, mst_pegawai.nip, mst_pegawai.golongan, ju.jabatan AS jabatan_label')
                ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
                ->where("REPLACE(REPLACE(REPLACE(mst_pegawai.nip, ' ', ''), '.', ''), '-', '')", $cleanNip)
                ->get()
                ->getRowArray();

            if ($row) {
                $res['pegawai_id']        = (int) $row['id'];
                $res['is_matched_master'] = 1;
                $res['official_name']     = $row['nama'];
                $res['official_nip']      = $row['nip'];
                $res['official_golongan'] = $row['golongan'];
                $res['official_jabatan']  = $row['jabatan_label'];
                return $res;
            }
        }

        // 2. Match by clean Name
        $cleanName = trim(preg_replace('/^(dr\.|ir\.|drs\.|dra\.)\s+/i', '', $name));
        $cleanName = trim(preg_replace('/,\s*(s\.t\.|st|s\.ars|s\.si|s\.ds|s\.sos|s\.e|se|a\.md|amd|m\.t|mt|s\.st)\.?$/i', '', $cleanName));

        $row = $db->table('mst_pegawai')
            ->select('mst_pegawai.id, mst_pegawai.nama, mst_pegawai.nip, mst_pegawai.golongan, ju.jabatan AS jabatan_label')
            ->join('mst_jabatan ju', 'ju.id = mst_pegawai.jabatan_utama_id', 'left')
            ->like('LOWER(mst_pegawai.nama)', strtolower($cleanName))
            ->get()
            ->getRowArray();

        if ($row) {
            $res['pegawai_id']        = (int) $row['id'];
            $res['is_matched_master'] = 1;
            $res['official_name']     = $row['nama'];
            $res['official_nip']      = $row['nip'];
            $res['official_golongan'] = $row['golongan'];
            $res['official_jabatan']  = $row['jabatan_label'];
            return $res;
        }

        return $res;
    }

    /**
     * Seed all verified Perjadin records
     */
    private function seedAllPerjadinPersonel(): void
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        // Look up packages
        $packages = [];
        if ($db->tableExists('trn_swakelola_paket')) {
            $rows = $db->table('trn_swakelola_paket')->get()->getResultArray();
            foreach ($rows as $r) {
                $packages[$r['kode_paket']] = $r;
            }
        }

        // Look up realisasi IDs by nomor_spm
        $realisasiMap = [];
        if ($db->tableExists('trn_swakelola_realisasi')) {
            $rRows = $db->table('trn_swakelola_realisasi')->get()->getResultArray();
            foreach ($rRows as $rr) {
                $spmClean = trim((string) ($rr['nomor_spm'] ?? ''));
                $realisasiMap[$spmClean] = (int) $rr['id'];
            }
        }

        // Load complete Perjadin data dataset
        $dataList = $this->getPerjadinDataset();

        foreach ($dataList as $item) {
            $kodePaket = $item['kode_paket'];
            $paket = $packages[$kodePaket] ?? null;
            $paketId = $paket ? (int) $paket['id'] : 1;
            $namaPaket = $paket ? $paket['nama_kegiatan'] : $item['nama_paket'];

            $spm = $item['nomor_spm'] ?? '';
            $realisasiId = $realisasiMap[$spm] ?? null;

            // Match with master pegawai
            $match = $this->matchWithMasterPegawai($item['nama_personel'], $item['nip'] ?? null);

            $db->table('trn_swakelola_perjadin_personel')->insert([
                'paket_id'          => $paketId,
                'realisasi_id'      => $realisasiId,
                'kode_paket'        => $kodePaket,
                'nama_paket'        => $namaPaket,
                'kode_akun'         => $item['kode_akun'],
                'nomor_spt'         => $item['nomor_spt'],
                'tanggal_spt'       => $item['tanggal_spt'],
                'nomor_spd'         => $item['nomor_spd'] ?? null,
                'nama_personel'     => $match['is_matched_master'] ? $match['official_name'] : $item['nama_personel'],
                'nip'               => $match['is_matched_master'] ? $match['official_nip'] : ($item['nip'] ?? null),
                'jabatan'           => $match['is_matched_master'] ? ($match['official_jabatan'] ?: $item['jabatan']) : ($item['jabatan'] ?? null),
                'instansi'          => $item['instansi'] ?? 'Satker Pelaksanaan Prasarana Strategis Riau',
                'golongan'          => $match['is_matched_master'] ? ($match['official_golongan'] ?: ($item['golongan'] ?? null)) : ($item['golongan'] ?? null),
                'pegawai_id'        => $match['pegawai_id'],
                'is_matched_master' => $match['is_matched_master'],
                'kota_tujuan'       => $item['kota_tujuan'],
                'tanggal_berangkat' => $item['tanggal_berangkat'],
                'tanggal_kembali'   => $item['tanggal_kembali'],
                'lama_hari'         => $item['lama_hari'] ?? 1,
                'maksud_tugas'      => $item['maksud_tugas'],
                'nomor_spm'         => $item['nomor_spm'],
                'tanggal_spm'       => $item['tanggal_spm'],
                'nomor_sp2d'        => $item['nomor_sp2d'],
                'nilai_biaya'       => $item['nilai_biaya'],
                'file_bukti'        => $item['file_bukti'],
                'keterangan'        => $item['keterangan'] ?? null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }

    /**
     * The verified Perjadin dataset extracted from scanned files & documents
     */
    private function getPerjadinDataset(): array
    {
        return \App\Models\Data\SwakelolaPerjadinData::all();
    }
}
