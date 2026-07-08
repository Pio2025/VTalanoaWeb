<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGuestIdToParticipants extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE meeting_participants ADD COLUMN guest_id VARCHAR(64) NULL DEFAULT NULL AFTER user_id');
        $this->db->query('ALTER TABLE meeting_participants ADD INDEX meeting_id_guest_id (meeting_id, guest_id)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE meeting_participants DROP INDEX meeting_id_guest_id');
        $this->db->query('ALTER TABLE meeting_participants DROP COLUMN guest_id');
    }
}
