<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginHistories extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('login_histories')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'attempted_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
                'is_success' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'failure_reason' => [
                    'type' => 'VARCHAR',
                    'constraint' => 190,
                    'null' => true,
                ],
                'username_input' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'full_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                ],
                'role' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => true,
                ],
                'account_active' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => true,
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                ],
                'user_agent' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'http_method' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => true,
                ],
                'request_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'referer' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'session_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 128,
                    'null' => true,
                ],
                'request_payload_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'server_context_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('attempted_at');
            $this->forge->addKey('is_success');
            $this->forge->addKey('username_input');
            $this->forge->addKey('user_id');
            $this->forge->createTable('login_histories', true);
        }

        if ($this->db->tableExists('audit_histories')) {
            $rows = $this->db->table('audit_histories')
                ->where('action_type', 'login')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            if ($rows !== []) {
                $batch = [];
                foreach ($rows as $row) {
                    $batch[] = [
                        'attempted_at' => (string) ($row['happened_at'] ?? date('Y-m-d H:i:s')),
                        'is_success' => 1,
                        'failure_reason' => null,
                        'username_input' => (string) ($row['username'] ?? ''),
                        'user_id' => (int) ($row['user_id'] ?? 0) ?: null,
                        'full_name' => (string) ($row['username'] ?? ''),
                        'role' => (string) ($row['role'] ?? ''),
                        'account_active' => 1,
                        'ip_address' => (string) ($row['ip_address'] ?? ''),
                        'user_agent' => (string) ($row['user_agent'] ?? ''),
                        'http_method' => 'POST',
                        'request_path' => 'masuk',
                        'referer' => null,
                        'session_id' => null,
                        'request_payload_json' => (string) ($row['request_data_json'] ?? ''),
                        'server_context_json' => null,
                        'created_at' => (string) ($row['created_at'] ?? date('Y-m-d H:i:s')),
                    ];
                }

                $this->db->table('login_histories')->insertBatch($batch);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('login_histories')) {
            $this->forge->dropTable('login_histories', true);
        }
    }
}
