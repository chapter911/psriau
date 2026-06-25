<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleDriveLinkFieldsToVerifikasiDokumen extends Migration
{
    protected $DBGroup = 'default';

    public function up()
    {
        $fields = [
            'is_google_drive_link' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '1=jika file dari Google Drive link user'
            ],
            'google_drive_source_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
                'comment'    => 'Link Google Drive asli dari user'
            ],
            'copied_to_project_drive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '1=jika sudah disalin ke Google Drive proyek'
            ],
            'copied_to_project_drive_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Waktu file disalin'
            ],
            'copied_to_project_drive_by' => [
                'type'       => 'INT',
                'null'       => true,
                'comment'    => 'User ID yang menyalin'
            ],
            'original_file_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Google Drive File ID dari link user'
            ],
        ];

        $this->forge->addColumn('trn_kontrak_simak_konsultasi_verifikasi_dokumen', $fields);
        $this->forge->addColumn('trn_kontrak_simak_konstruksi_verifikasi_dokumen', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('trn_kontrak_simak_konsultasi_verifikasi_dokumen', [
            'is_google_drive_link',
            'google_drive_source_url',
            'copied_to_project_drive',
            'copied_to_project_drive_at',
            'copied_to_project_drive_by',
            'original_file_id',
        ]);
        $this->forge->dropColumn('trn_kontrak_simak_konstruksi_verifikasi_dokumen', [
            'is_google_drive_link',
            'google_drive_source_url',
            'copied_to_project_drive',
            'copied_to_project_drive_at',
            'copied_to_project_drive_by',
            'original_file_id',
        ]);
    }
}
