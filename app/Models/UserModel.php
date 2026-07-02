<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'user_id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'uuid', 'fname', 'lname', 'email', 'username', 'password',
        'profile_photo', 'timezone', 'user_status', 'auth_type', 'email_verified_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'email'    => 'required|valid_email|max_length[180]',
        'fname'    => 'required|max_length[80]',
        'lname'    => 'required|max_length[80]',
        'username' => 'required|alpha_numeric|min_length[3]|max_length[60]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    public function findByUuid(string $uuid): ?array
    {
        return $this->where('uuid', $uuid)->first();
    }

    public function getPublicProfile(int $userId): ?array
    {
        return $this->select('user_id, uuid, fname, lname, username, profile_photo, timezone, user_status, created_at')
                    ->find($userId);
    }
}
