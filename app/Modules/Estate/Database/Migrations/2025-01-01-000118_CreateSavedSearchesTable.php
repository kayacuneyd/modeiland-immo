<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSavedSearchesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INTEGER', 'auto_increment' => true],
            'seeker_id'      => ['type' => 'INTEGER', 'null' => false],
            'label'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'filters_json'   => ['type' => 'TEXT', 'null' => false, 'default' => '{}'],
            'alert_enabled'  => ['type' => 'INTEGER', 'default' => 0],  // 0|1 (SQLite bool)
            'last_alerted_at'=> ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('seeker_id', 'seekers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('saved_searches', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('saved_searches', true);
    }
}
