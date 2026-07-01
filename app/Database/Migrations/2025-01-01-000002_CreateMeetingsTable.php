<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'meeting_id'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'meeting_uuid'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false],
            'meeting_token'    => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'host_user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'password'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'scheduled_start'  => ['type' => 'DATETIME', 'null' => false],
            'scheduled_end'    => ['type' => 'DATETIME', 'null' => false],
            'actual_start'     => ['type' => 'DATETIME', 'null' => true],
            'actual_end'       => ['type' => 'DATETIME', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['Scheduled', 'Active', 'Ended', 'Cancelled'], 'default' => 'Scheduled'],
            'waiting_room'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'allow_recording'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'max_participants' => ['type' => 'SMALLINT', 'default' => 100],
            'created_at'       => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'       => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('meeting_id', true);
        $this->forge->addUniqueKey('meeting_uuid');
        $this->forge->addUniqueKey('meeting_token');
        $this->forge->addKey('host_user_id');
        $this->forge->addForeignKey('host_user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('meetings');
    }

    public function down(): void
    {
        $this->forge->dropTable('meetings', true);
    }
}
