<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table      = 'password_resets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['email', 'token_hash', 'expires_at'];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    // Max 3 reset requests per email within 15 minutes
    private const RATE_LIMIT        = 3;
    private const RATE_WINDOW_MINS  = 15;
    private const EXPIRY_MINS       = 60;

    public function createToken(string $email): string
    {
        // Delete any existing tokens for this email
        $this->where('email', $email)->delete();

        $plainToken = bin2hex(random_bytes(32));
        $this->insert([
            'email'      => $email,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_MINS . ' minutes')),
        ]);

        return $plainToken;
    }

    public function findValidByToken(string $plainToken): ?array
    {
        return $this->where('token_hash', hash('sha256', $plainToken))
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    public function deleteByToken(string $plainToken): void
    {
        $this->where('token_hash', hash('sha256', $plainToken))->delete();
    }

    public function isRateLimited(string $email): bool
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . self::RATE_WINDOW_MINS . ' minutes'));
        $count = $this->where('email', $email)
                      ->where('created_at >', $since)
                      ->countAllResults();
        return $count >= self::RATE_LIMIT;
    }

    public function purgeExpired(): void
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }
}
