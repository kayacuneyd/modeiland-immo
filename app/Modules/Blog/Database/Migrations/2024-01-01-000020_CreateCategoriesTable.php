<?php

namespace App\Modules\Blog\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'   => ['type' => 'INTEGER', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'lang' => ['type' => 'VARCHAR', 'constraint' => 8,   'null' => false, 'default' => 'tr'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['slug', 'lang']);
        $this->forge->createTable('categories', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('categories', true);
    }
}
