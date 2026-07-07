<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\JWTService;

class AuthController extends BaseController
{
    private UserModel $userModel;
    private JWTService $jwtService;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->jwtService = new JWTService();
    }

    // ── Routes ────────────────────────────────────────────────────────────────

    /** POST /api/auth/register */
    public function register(): mixed
    {
        $data = $this->request->getJSON(true) ?? [];

        // Flutter sends a single "name" field — split it into fname / lname
        $fullName = trim($data['name'] ?? '');
        if ($fullName && empty($data['fname'])) {
            $parts         = explode(' ', $fullName, 2);
            $data['fname'] = $parts[0];
            $data['lname'] = $parts[1] ?? $parts[0];
        }

        $fname    = trim($data['fname'] ?? '');
        $lname    = trim($data['lname'] ?? $fname);
        $email    = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        $errors = [];
        if (!$fname)                                      $errors['name']     = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors['email']    = 'A valid email is required.';
        if (strlen($password) < 8)                        $errors['password'] = 'Password must be at least 8 characters.';

        if ($errors) {
            return $this->response->setJSON(['error' => 'Validation failed.', 'details' => $errors])->setStatusCode(422);
        }

        if ($this->userModel->findByEmail($email)) {
            return $this->response->setJSON(['error' => 'An account with this email already exists.'])->setStatusCode(409);
        }

        $userId = $this->userModel->insert([
            'uuid'      => $this->uuid(),
            'fname'     => $fname,
            'lname'     => $lname,
            'email'     => $email,
            'username'  => $this->uniqueUsername($email),
            'password'  => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'timezone'  => $data['timezone'] ?? 'UTC',
            'auth_type' => 'local',
        ]);

        $user = $this->userModel->find($userId);
        [$token, $refresh] = $this->issueTokens($user);

        return $this->response
            ->setJSON(['token' => $token, 'refresh_token' => $refresh, 'user' => $this->safeUser($user)])
            ->setStatusCode(201);
    }

    /** POST /api/auth/login */
    public function login(): mixed
    {
        $data     = $this->request->getJSON(true) ?? [];
        $email    = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $user     = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            return $this->response->setJSON(['error' => 'Invalid email or password.'])->setStatusCode(401);
        }

        if ($user['user_status'] !== 'Active') {
            return $this->response->setJSON(['error' => 'Your account is suspended. Contact support.'])->setStatusCode(403);
        }

        [$token, $refresh] = $this->issueTokens($user);
        return $this->response->setJSON(['token' => $token, 'refresh_token' => $refresh, 'user' => $this->safeUser($user)]);
    }

    /** POST /api/auth/logout */
    public function logout(): mixed
    {
        // JWT is stateless — the client discards the token locally.
        return $this->response->setJSON(['message' => 'Logged out successfully.']);
    }

    /** GET /api/auth/me */
    public function me(): mixed
    {
        $user = session()->get('auth_user');
        if (!$user) {
            return $this->response->setJSON(['error' => 'Unauthenticated.'])->setStatusCode(401);
        }
        return $this->response->setJSON(['user' => $this->safeUser($user)]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function issueTokens(array $user): array
    {
        $payload = ['user_id' => $user['user_id'], 'email' => $user['email'], 'uuid' => $user['uuid'] ?? null];
        return [
            $this->jwtService->generateToken($payload),
            $this->jwtService->generateRefreshToken($payload),
        ];
    }

    private function safeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }

    private function uniqueUsername(string $email): string
    {
        $base   = preg_replace('/[^a-z0-9]/', '', strtolower(explode('@', $email)[0])) ?: 'user';
        $name   = $base;
        $suffix = 1;
        while ($this->userModel->findByUsername($name)) {
            $name = $base . $suffix++;
        }
        return $name;
    }

    private function uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
