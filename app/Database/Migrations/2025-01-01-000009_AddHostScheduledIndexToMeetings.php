<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHostScheduledIndexToMeetings extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE meetings ADD INDEX host_user_id_scheduled_start (host_user_id, scheduled_start)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE meetings DROP INDEX host_user_id_scheduled_start');
    }
}
