<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatAttachmentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'attachment_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'message_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'meeting_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'file_url'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false],
            'file_name'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'mime_type'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'file_size'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'    => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('attachment_id', true);
        $this->forge->addKey('message_id');
        $this->forge->addKey('meeting_id');
        $this->forge->addForeignKey('message_id', 'meeting_messages', 'message_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('meeting_id', 'meetings', 'meeting_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('meeting_message_attachments');
    }

    public function down(): void
    {
        $this->forge->dropTable('meeting_message_attachments', true);
    }
}
