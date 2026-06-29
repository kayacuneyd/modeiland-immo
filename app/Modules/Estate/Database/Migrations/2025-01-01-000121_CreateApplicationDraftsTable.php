<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationDraftsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INTEGER', 'auto_increment' => true],
            'seeker_id'      => ['type' => 'INTEGER', 'null' => false],
            'listing_id'     => ['type' => 'INTEGER', 'null' => false],
            'cover_letter'   => ['type' => 'TEXT', 'null' => true],
            'checklist_json' => ['type' => 'TEXT', 'null' => true],
            'generated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['seeker_id', 'listing_id']);
        $this->forge->addForeignKey('seeker_id', 'seekers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('listing_id', 'listings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('application_drafts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('application_drafts', true);
    }
}
