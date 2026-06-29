<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateListingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INTEGER', 'auto_increment' => true],
            'owner_id'         => ['type' => 'INTEGER', 'null' => false],
            'source_url'       => ['type' => 'TEXT', 'null' => true],
            'status'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'kaltmiete'        => ['type' => 'INTEGER', 'null' => true],
            'warmmiete'        => ['type' => 'INTEGER', 'null' => true],
            'nebenkosten'      => ['type' => 'INTEGER', 'null' => true],
            'deposit'          => ['type' => 'INTEGER', 'null' => true],
            'rooms'            => ['type' => 'REAL', 'null' => true],
            'm2'               => ['type' => 'REAL', 'null' => true],
            'location_text'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'location_approx'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'available_from'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'ai_description'   => ['type' => 'TEXT', 'null' => true],
            'source_text_raw'  => ['type' => 'TEXT', 'null' => true],
            'type'             => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'rent'],
            'is_first_free'    => ['type' => 'INTEGER', 'default' => 0],
            'ai_import_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'none'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listings', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('listings', true);
    }
}
