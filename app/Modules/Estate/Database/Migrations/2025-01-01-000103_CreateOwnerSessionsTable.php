<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOwnerSessionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INTEGER', 'auto_increment' => true],
            'owner_id'     => ['type' => 'INTEGER', 'null' => false],
            'session_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'user_agent'   => ['type' => 'TEXT', 'null' => true],
            'ip'           => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'expires_at'   => ['type' => 'DATETIME', 'null' => false],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('owner_sessions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('owner_sessions', true);
    }
}
