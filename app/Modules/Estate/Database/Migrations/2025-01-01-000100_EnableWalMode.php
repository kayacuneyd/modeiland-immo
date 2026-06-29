<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnableWalMode extends Migration
{
    public function up(): void
    {
        $this->db->query('PRAGMA journal_mode=WAL');
        $this->db->query('PRAGMA foreign_keys=ON');
        $this->db->query('PRAGMA busy_timeout=5000');
    }

    public function down(): void
    {
        $this->db->query('PRAGMA journal_mode=DELETE');
    }
}
