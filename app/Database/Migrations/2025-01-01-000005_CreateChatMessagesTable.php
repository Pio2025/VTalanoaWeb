<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatMessagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'message_id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'meeting_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'sender_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sender_name'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'message'      => ['type' => 'TEXT', 'null' => false],
            'is_private'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'recipient_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sent_at'      => ['type' => 'DATETIME', 'null' => false],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('message_id', true);
        $this->forge->addKey('meeting_id');
        $this->forge->addForeignKey('meeting_id', 'meetings', 'meeting_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chat_messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('chat_messages', true);
    }
}
