<?php

namespace App\Modules\Pages\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'lang'             => ['type' => 'VARCHAR', 'constraint' => 8,   'null' => false, 'default' => 'tr'],
            'content'          => ['type' => 'TEXT',    'null' => true],
            'meta_title'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta_description' => ['type' => 'TEXT',    'null' => true],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 30,  'null' => false, 'default' => 'draft'],
            'sort_order'       => ['type' => 'INTEGER', 'null' => false, 'default' => 0],
            'media_id'         => ['type' => 'INTEGER', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['slug', 'lang']);
        $this->forge->createTable('pages', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('pages', true);
    }
}
