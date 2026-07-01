<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialLoginModel extends Model
{
    protected $table      = 'social_logins';
    protected $primaryKey = 'social_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id', 'provider', 'provider_user_id', 'provider_email',
        'access_token', 'refresh_token', 'token_expires_at', 'linked_at',
    ];

    protected $useTimestamps = false;

    public function findByProvider(string $provider, string $providerUserId): ?array
    {
        return $this->where('provider', $provider)
                    ->where('provider_user_id', $providerUserId)
                    ->first();
    }

    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    public function unlinkProvider(int $userId, string $provider): bool
    {
        return $this->where('user_id', $userId)->where('provider', $provider)->delete();
    }
}
