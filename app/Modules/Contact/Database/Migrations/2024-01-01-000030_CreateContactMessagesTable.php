<?php

namespace App\Modules\Contact\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => false],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => false],
            'subject'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'message'    => ['type' => 'TEXT',    'null' => false],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45,  'null' => true],
            'is_read'    => ['type' => 'INTEGER', 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('contact_messages', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('contact_messages', true);
    }
}
