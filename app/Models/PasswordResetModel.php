<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table      = 'password_resets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['email', 'token_hash', 'expires_at', 'created_at'];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    private const EXPIRY_MINS      = 60;
    private const COOLDOWN_SECONDS = 120; // min gap between resends

    /**
     * Upsert: update existing row for this email or insert a new one.
     * Returns the plain-text token (only ever sent in the email link).
     */
    public function upsertToken(string $email): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $now        = date('Y-m-d H:i:s');
        $expires    = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_MINS . ' minutes'));

        $existing = $this->where('email', $email)->first();

        if ($existing) {
            $this->update($existing['id'], [
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => $expires,
                'created_at' => $now,
            ]);
        } else {
            $this->insert([
                'email'      => $email,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => $expires,
                'created_at' => $now,
            ]);
        }

        return $plainToken;
    }

    /**
     * True if a reset link was sent for this email within the cooldown window.
     * Prevents accidental or intentional spam.
     */
    public function isRecentlySent(string $email): bool
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . self::COOLDOWN_SECONDS . ' seconds'));
        return $this->where('email', $email)
                    ->where('created_at >', $since)
                    ->first() !== null;
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

    public function purgeExpired(): void
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }
}
