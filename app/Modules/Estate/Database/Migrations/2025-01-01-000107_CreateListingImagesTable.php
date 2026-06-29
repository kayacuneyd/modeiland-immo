<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateListingImagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'listing_id' => ['type' => 'INTEGER', 'null' => false],
            'path'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false],
            'sort'       => ['type' => 'INTEGER', 'default' => 0],
            'approved'   => ['type' => 'INTEGER', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_images', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('listing_images', true);
    }
}
