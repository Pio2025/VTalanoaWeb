<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingParticipantsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'participant_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'meeting_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'user_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'guest_name'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'guest_email'    => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'role'           => ['type' => 'ENUM', 'constraint' => ['Host', 'Co-Host', 'Attendee'], 'default' => 'Attendee'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Waiting', 'Admitted', 'Removed', 'Left'], 'default' => 'Waiting'],
            'joined_at'      => ['type' => 'DATETIME', 'null' => true],
            'left_at'        => ['type' => 'DATETIME', 'null' => true],
            'is_muted'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_video_off'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'     => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'     => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('participant_id', true);
        $this->forge->addKey('meeting_id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('meeting_id', 'meetings', 'meeting_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('meeting_participants');
    }

    public function down(): void
    {
        $this->forge->dropTable('meeting_participants', true);
    }
}
