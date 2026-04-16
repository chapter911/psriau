<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpiresAtToKontrakSimakShareTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_simak_share')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_simak_share');
        foreach ($fields as $field) {
            if (strtolower((string) ($field->name ?? '')) === 'expires_at') {
                return;
            }
        }

        $this->forge->addColumn('trn_kontrak_simak_share', [
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'share_token',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kontrak_simak_share')) {
            return;
        }

        $fields = $this->db->getFieldData('trn_kontrak_simak_share');
        $hasColumn = false;
        foreach ($fields as $field) {
            if (strtolower((string) ($field->name ?? '')) === 'expires_at') {
                $hasColumn = true;
                break;
            }
        }

        if ($hasColumn) {
            $this->forge->dropColumn('trn_kontrak_simak_share', 'expires_at');
        }
    }
}
