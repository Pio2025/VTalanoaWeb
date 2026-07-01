<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecordingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'recording_id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'meeting_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'user_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'file_name'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'duration_seconds' => ['type' => 'INT', 'null' => true],
            'started_at'       => ['type' => 'DATETIME', 'null' => false],
            'ended_at'         => ['type' => 'DATETIME', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['Recording', 'Completed', 'Aborted'], 'default' => 'Recording'],
            'created_at'       => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'       => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('recording_id', true);
        $this->forge->addKey('meeting_id');
        $this->forge->addForeignKey('meeting_id', 'meetings', 'meeting_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('recordings');
    }

    public function down(): void
    {
        $this->forge->dropTable('recordings', true);
    }
}
