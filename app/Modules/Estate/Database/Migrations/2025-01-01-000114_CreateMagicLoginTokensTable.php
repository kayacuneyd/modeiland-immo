<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMagicLoginTokensTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'owner_id'   => ['type' => 'INTEGER', 'null' => false],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'expires_at' => ['type' => 'DATETIME', 'null' => false],
            'used_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('owner_id', 'owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('magic_login_tokens', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('magic_login_tokens', true);
    }
}
