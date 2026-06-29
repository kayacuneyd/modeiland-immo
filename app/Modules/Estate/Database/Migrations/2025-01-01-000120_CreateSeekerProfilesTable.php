<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeekerProfilesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INTEGER', 'auto_increment' => true],
            'seeker_id'         => ['type' => 'INTEGER', 'null' => false],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'move_in_date'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'household_size'    => ['type' => 'INTEGER', 'null' => true],
            'occupation'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'income_range_cents'=> ['type' => 'INTEGER', 'null' => true],  // monthly net income upper bound
            'pets'              => ['type' => 'INTEGER', 'default' => 0],  // 0|1
            'notes'             => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('seeker_id');
        $this->forge->addForeignKey('seeker_id', 'seekers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('seeker_profiles', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('seeker_profiles', true);
    }
}
