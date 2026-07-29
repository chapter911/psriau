<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddManualPegawaiToStrukturOrganisasi extends Migration
{
    public function up()
    {
        $db = $this->db;

        if ($db->tableExists('tb_struktur_organisasi')) {
            $forge = \Config\Database::forge();

            $fields = [];

            if (! $db->fieldExists('nama_manual', 'tb_struktur_organisasi')) {
                $fields['nama_manual'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'pegawai_id',
                ];
            }

            if (! $db->fieldExists('nip_manual', 'tb_struktur_organisasi')) {
                $fields['nip_manual'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'nama_manual',
                ];
            }

            if (! $db->fieldExists('foto_manual', 'tb_struktur_organisasi')) {
                $fields['foto_manual'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'nip_manual',
                ];
            }

            if (! empty($fields)) {
                $forge->addColumn('tb_struktur_organisasi', $fields);
            }

            // Update sample root node Level 1 & Level 2 with standard manual titles if pegawai_id is empty
            $db->table('tb_struktur_organisasi')
                ->where('id', 1)
                ->where('pegawai_id IS NULL')
                ->update([
                    'nama_manual' => 'Ir. DODY HANGGODO, M.P.',
                    'nip_manual'  => 'Menteri Pekerjaan Umum',
                ]);

            $db->table('tb_struktur_organisasi')
                ->where('id', 2)
                ->where('pegawai_id IS NULL')
                ->update([
                    'nama_manual' => 'KUSWARA, S.T., M.A.',
                    'nip_manual'  => 'Plt. Direktur Jenderal Prasarana Strategis',
                ]);
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->tableExists('tb_struktur_organisasi')) {
            $forge = \Config\Database::forge();
            $columnsToDrop = [];

            if ($db->fieldExists('nama_manual', 'tb_struktur_organisasi')) {
                $columnsToDrop[] = 'nama_manual';
            }
            if ($db->fieldExists('nip_manual', 'tb_struktur_organisasi')) {
                $columnsToDrop[] = 'nip_manual';
            }
            if ($db->fieldExists('foto_manual', 'tb_struktur_organisasi')) {
                $columnsToDrop[] = 'foto_manual';
            }

            if (! empty($columnsToDrop)) {
                $forge->dropColumn('tb_struktur_organisasi', $columnsToDrop);
            }
        }
    }
}
