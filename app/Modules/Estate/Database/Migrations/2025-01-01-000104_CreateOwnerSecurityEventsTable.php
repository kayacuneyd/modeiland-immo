<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOwnerSecurityEventsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'owner_id'   => ['type' => 'INTEGER', 'null' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'ip'         => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'meta'       => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('owner_security_events', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('owner_security_events', true);
    }
}
