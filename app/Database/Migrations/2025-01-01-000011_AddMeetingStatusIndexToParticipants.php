<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMeetingStatusIndexToParticipants extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE meeting_participants ADD INDEX meeting_id_status (meeting_id, status)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE meeting_participants DROP INDEX meeting_id_status');
    }
}
