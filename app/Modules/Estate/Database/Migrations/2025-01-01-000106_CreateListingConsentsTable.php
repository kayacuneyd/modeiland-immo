<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateListingConsentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INTEGER', 'auto_increment' => true],
            'owner_id'              => ['type' => 'INTEGER', 'null' => false],
            'listing_id'            => ['type' => 'INTEGER', 'null' => false],
            'consent_version'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1.0'],
            'accepted_at'           => ['type' => 'DATETIME', 'null' => false],
            'ip_address'            => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'            => ['type' => 'TEXT', 'null' => true],
            'approved_photos'       => ['type' => 'INTEGER', 'default' => 0],
            'approved_contact_method' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'platform'],
            'approved_ai_rewrite'   => ['type' => 'INTEGER', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_consents', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('listing_consents', true);
    }
}
