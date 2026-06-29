<?php

namespace App\Modules\Estate\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCloudflareFieldsToListingImages extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE listing_images ADD COLUMN cf_image_id VARCHAR(255) NULL DEFAULT NULL');
        $this->db->query('ALTER TABLE listing_images ADD COLUMN cf_url VARCHAR(1000) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // SQLite DROP COLUMN requires v3.35+; safe to leave for rollback
    }
}
