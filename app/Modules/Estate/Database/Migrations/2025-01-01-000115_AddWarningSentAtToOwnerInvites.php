<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarningSentAtToOwnerInvites extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE owner_invites ADD COLUMN warning_sent_at DATETIME NULL DEFAULT NULL');
        $this->db->query('ALTER TABLE owner_invites ADD COLUMN updated_at DATETIME NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // SQLite does not support DROP COLUMN in older versions — safe to leave for rollback
    }
}
