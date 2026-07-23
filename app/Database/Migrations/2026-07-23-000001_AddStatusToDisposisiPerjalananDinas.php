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
        if (! $db->fieldExists('status', 'disposisi_perjalanan_dinas')) {
            $fields['status'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'disetujui', 'ditolak'],
                'default'    => 'pending',
                'after'      => 'diketahui_pegawai_id',
            ];
        }

        if (! $db->fieldExists('approval_token', 'disposisi_perjalanan_dinas')) {
            $fields['approval_token'] = [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status',
            ];
        }

        if (! $db->fieldExists('catatan_penolakan', 'disposisi_perjalanan_dinas')) {
            $fields['catatan_penolakan'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approval_token',
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
        if ($db->fieldExists('status', 'disposisi_perjalanan_dinas')) {
            $columns[] = 'status';
        }
        if ($db->fieldExists('approval_token', 'disposisi_perjalanan_dinas')) {
            $columns[] = 'approval_token';
        }
        if ($db->fieldExists('catatan_penolakan', 'disposisi_perjalanan_dinas')) {
            $columns[] = 'catatan_penolakan';
        }

        if ($columns !== []) {
            $this->forge->dropColumn('disposisi_perjalanan_dinas', $columns);
        }
    }
}
