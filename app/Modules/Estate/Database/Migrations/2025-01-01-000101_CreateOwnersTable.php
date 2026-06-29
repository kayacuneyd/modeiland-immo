<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOwnersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INTEGER', 'auto_increment' => true],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'lead'],
            'display_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'phone'        => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'password_hash'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'login_method' => ['type' => 'VARCHAR', 'constraint' => 30,  'default' => 'invite'],
            'source_url'   => ['type' => 'TEXT', 'null' => true],
            'outreach_note'=> ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('owners', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('owners', true);
    }
}
