<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToOwnerInvites extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('owner_invites', [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'invite',
                'after'      => 'owner_id',
                'null'       => false,
            ],
        ]);

        // Backfill existing rows
        $this->db->query("UPDATE owner_invites SET type = 'invite' WHERE type IS NULL OR type = ''");
    }

    public function down(): void
    {
        $this->forge->dropColumn('owner_invites', 'type');
    }
}
