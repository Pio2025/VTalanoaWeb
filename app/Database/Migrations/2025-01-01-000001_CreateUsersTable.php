<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'user_id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'uuid'              => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'fname'             => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'lname'             => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => false],
            'username'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => false],
            'password'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'profile_photo'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'timezone'          => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => 'UTC'],
            'user_status'       => ['type' => 'ENUM', 'constraint' => ['Active', 'Inactive', 'Suspended'], 'default' => 'Active'],
            'auth_type'         => ['type' => 'ENUM', 'constraint' => ['local', 'social', 'both'], 'default' => 'local'],
            'email_verified_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'created_at'        => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'        => ['type' => 'TIMESTAMP', 'null' => true],
        ]);

        $this->forge->addKey('user_id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
