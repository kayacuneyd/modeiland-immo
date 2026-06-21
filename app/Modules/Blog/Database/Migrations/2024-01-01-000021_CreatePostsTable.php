<?php

namespace App\Modules\Blog\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INTEGER', 'auto_increment' => true],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'lang'             => ['type' => 'VARCHAR', 'constraint' => 8,   'null' => false, 'default' => 'tr'],
            'excerpt'          => ['type' => 'TEXT',    'null' => true],
            'content'          => ['type' => 'TEXT',    'null' => true],
            'category_id'      => ['type' => 'INTEGER', 'null' => true],
            'media_id'         => ['type' => 'INTEGER', 'null' => true],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 30,  'null' => false, 'default' => 'draft'],
            'published_at'     => ['type' => 'DATETIME', 'null' => true],
            'meta_title'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta_description' => ['type' => 'TEXT',    'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['slug', 'lang']);
        $this->forge->createTable('posts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('posts', true);
    }
}
