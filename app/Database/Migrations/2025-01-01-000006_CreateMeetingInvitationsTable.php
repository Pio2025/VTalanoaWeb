<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingInvitationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'invite_id'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'meeting_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'invited_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'invitee_email'   => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => false],
            'invitee_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'token'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Pending', 'Accepted', 'Declined'], 'default' => 'Pending'],
            'sent_at'         => ['type' => 'DATETIME', 'null' => false],
            'responded_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'      => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('invite_id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('meeting_id');
        $this->forge->addForeignKey('meeting_id', 'meetings', 'meeting_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invited_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('meeting_invitations');
    }

    public function down(): void
    {
        $this->forge->dropTable('meeting_invitations', true);
    }
}
