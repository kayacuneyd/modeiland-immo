<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMatchReasonsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INTEGER', 'auto_increment' => true],
            'listing_id'   => ['type' => 'INTEGER', 'null' => false],
            'filters_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            'score_total'  => ['type' => 'INTEGER', 'null' => false, 'default' => 0],
            'reason_text'  => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['listing_id', 'filters_hash']);
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('match_reasons', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('match_reasons', true);
    }
}
