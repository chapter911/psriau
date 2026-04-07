<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoUrlToHomeSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('home_settings', [
            'logo_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'official_name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('home_settings', 'logo_url');
    }
}
