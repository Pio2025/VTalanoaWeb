<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSocialLoginsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'social_id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'provider'         => ['type' => 'ENUM', 'constraint' => ['google', 'facebook', 'microsoft', 'apple'], 'null' => false],
            'provider_user_id' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => false],
            'provider_email'   => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'access_token'     => ['type' => 'TEXT', 'null' => true],
            'refresh_token'    => ['type' => 'TEXT', 'null' => true],
            'token_expires_at' => ['type' => 'DATETIME', 'null' => true],
            'linked_at'        => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('social_id', true);
        $this->forge->addUniqueKey(['provider', 'provider_user_id']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('social_logins');
    }

    public function down(): void
    {
        $this->forge->dropTable('social_logins', true);
    }
}
