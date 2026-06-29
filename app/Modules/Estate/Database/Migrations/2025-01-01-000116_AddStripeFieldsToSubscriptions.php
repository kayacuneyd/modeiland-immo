<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStripeFieldsToSubscriptions extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE subscriptions ADD COLUMN stripe_subscription_id VARCHAR(255) NULL DEFAULT NULL');
        $this->db->query('ALTER TABLE subscriptions ADD COLUMN stripe_session_id VARCHAR(255) NULL DEFAULT NULL');
        $this->db->query('ALTER TABLE subscriptions ADD COLUMN stripe_customer_id VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // SQLite DROP COLUMN requires version 3.35+; safe to leave for rollback
    }
}
