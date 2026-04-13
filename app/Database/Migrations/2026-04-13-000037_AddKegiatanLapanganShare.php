<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKegiatanLapanganShare extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('kegiatan_lapangan_shares')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'activity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'share_token' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addUniqueKey('activity_id', 'uniq_kegiatan_lapangan_share_activity');
        $this->forge->addUniqueKey('share_token', 'uniq_kegiatan_lapangan_share_token');

        $this->forge->addForeignKey('activity_id', 'kegiatan_lapangan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kegiatan_lapangan_shares', true);
    }

    public function down()
    {
        if (! $this->db->tableExists('kegiatan_lapangan_shares')) {
            return;
        }

        $this->forge->dropTable('kegiatan_lapangan_shares', true);
    }
}
