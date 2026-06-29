<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOwnerListingChargesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INTEGER', 'auto_increment' => true],
            'owner_id'            => ['type' => 'INTEGER', 'null' => false],
            'listing_id'          => ['type' => 'INTEGER', 'null' => false],
            'amount_cents'        => ['type' => 'INTEGER', 'null' => false],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'provider_payment_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('owner_listing_charges', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('owner_listing_charges', true);
    }
}
