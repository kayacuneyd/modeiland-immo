<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'listing_id' => ['type' => 'INTEGER', 'null' => false],
            'seeker_id'  => ['type' => 'INTEGER', 'null' => false],
            'owner_id'   => ['type' => 'INTEGER', 'null' => false],
            'body'       => ['type' => 'TEXT', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('seeker_id', 'seekers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('messages', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('messages', true);
    }
}
