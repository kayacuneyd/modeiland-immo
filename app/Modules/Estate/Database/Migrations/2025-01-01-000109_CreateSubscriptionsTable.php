<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INTEGER', 'auto_increment' => true],
            'seeker_id'            => ['type' => 'INTEGER', 'null' => false],
            'provider_customer_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'plan'                 => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'basic'],
            'status'               => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'current_period_end'   => ['type' => 'DATETIME', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('seeker_id', 'seekers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('subscriptions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('subscriptions', true);
    }
}
