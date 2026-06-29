<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeekersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INTEGER', 'auto_increment' => true],
            'email'               => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'password_hash'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'login_method'        => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'magic_link'],
            'subscription_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'free'],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('seekers', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('seekers', true);
    }
}
