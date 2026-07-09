<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccessFlagsToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'akses_web' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'is_active',
            ],
            'akses_mobile' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'akses_web',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['akses_web', 'akses_mobile']);
    }
}
