<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToDisposisiPerjalananDinas extends Migration
{
    public function up()
    {
        $db = $this->db;
        if (! $db->tableExists('disposisi_perjalanan_dinas')) {
            return;
        }

        $fields = [];
        if (! $db->fieldExists('status_menyetujui', 'disposisi_perjalanan_dinas')) {
            $fields['status_menyetujui'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'diketahui_pegawai_id',
            ];
        }

        if (! $db->fieldExists('status_diketahui', 'disposisi_perjalanan_dinas')) {
            $fields['status_diketahui'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'status_menyetujui',
            ];
        }

        if (! $db->fieldExists('token_menyetujui', 'disposisi_perjalanan_dinas')) {
            $fields['token_menyetujui'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status_diketahui',
            ];
        }

        if (! $db->fieldExists('token_diketahui', 'disposisi_perjalanan_dinas')) {
            $fields['token_diketahui'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'token_menyetujui',
            ];
        }

        if (! $db->fieldExists('catatan_penolakan', 'disposisi_perjalanan_dinas')) {
            $fields['catatan_penolakan'] = [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'token_diketahui',
            ];
        }

        if (! $db->fieldExists('status', 'disposisi_perjalanan_dinas')) {
            $fields['status'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'catatan_penolakan',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('disposisi_perjalanan_dinas', $fields);
        }
    }

    public function down()
    {
        $db = $this->db;
        if (! $db->tableExists('disposisi_perjalanan_dinas')) {
            return;
        }

        $columns = [];
        foreach (['status_menyetujui', 'status_diketahui', 'token_menyetujui', 'token_diketahui', 'catatan_penolakan', 'status'] as $col) {
            if ($db->fieldExists($col, 'disposisi_perjalanan_dinas')) {
                $columns[] = $col;
            }
        }

        if ($columns !== []) {
            $this->forge->dropColumn('disposisi_perjalanan_dinas', $columns);
        }
    }
}
