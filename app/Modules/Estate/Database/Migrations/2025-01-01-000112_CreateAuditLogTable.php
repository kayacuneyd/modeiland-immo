<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLogTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'actor_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'actor_id'   => ['type' => 'INTEGER', 'null' => true],
            'action'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'target'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'meta'       => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('audit_log', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_log', true);
    }
}
