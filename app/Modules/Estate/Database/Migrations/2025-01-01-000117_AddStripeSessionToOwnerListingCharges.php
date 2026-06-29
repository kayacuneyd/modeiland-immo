<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStripeSessionToOwnerListingCharges extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE owner_listing_charges ADD COLUMN stripe_session_id VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // SQLite DROP COLUMN requires version 3.35+; safe to leave for rollback
    }
}
