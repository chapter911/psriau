<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTimTeknisPpkToKontrakPaket extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kontrak_paket')) {
            return;
        }

        if (! $this->db->fieldExists('tim_teknis_ppk', 'trn_kontrak_paket')) {
            $this->forge->addColumn('trn_kontrak_paket', [
                'tim_teknis_ppk' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'default' => null,
                    'comment' => 'JSON array nama Tim Teknis PPK untuk dokumen BAPHP per paket',
                    'after'   => 'tugas_tanggung_jawab',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_kontrak_paket') && $this->db->fieldExists('tim_teknis_ppk', 'trn_kontrak_paket')) {
            $this->forge->dropColumn('trn_kontrak_paket', 'tim_teknis_ppk');
        }
    }
}

