<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService
{
    private string $secret;
    private int $expiry;
    private int $refreshExpiry;
    private string $algorithm = 'HS256';

    public function __construct()
    {
        $this->secret       = $_ENV['JWT_SECRET'] ?? env('JWT_SECRET', 'navulimeet_secret');
        $this->expiry       = (int) (env('JWT_EXPIRY', 28800));
        $this->refreshExpiry = (int) (env('JWT_REFRESH_EXPIRY', 2592000));
    }

    public function generateToken(array $payload): string
    {
        $now = time();
        $data = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $this->expiry,
            'iss' => 'vtalanoa',
        ]);
        return JWT::encode($data, $this->secret, $this->algorithm);
    }

    public function generateRefreshToken(array $payload): string
    {
        $now = time();
        $data = [
            'user_id' => $payload['user_id'],
            'type'    => 'refresh',
            'iat'     => $now,
            'exp'     => $now + $this->refreshExpiry,
        ];
        return JWT::encode($data, $this->secret, $this->algorithm);
    }

    public function decode(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algorithm));
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTokenFromRequest(): ?string
    {
        $request = service('request');

        // Check Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Check cookie
        $cookie = $request->getCookie('nm_token');
        if (!empty($cookie)) {
            return $cookie;
        }

        // Check session
        $session = session();
        if ($session->has('nm_token')) {
            return $session->get('nm_token');
        }

        return null;
    }

    public function setAuthCookies(string $token, string $refreshToken): void
    {
        // Store in session — reliable across redirects on web routes
        session()->set('nm_token', $token);
        session()->set('nm_refresh', $refreshToken);

        // Also set as HttpOnly cookies for API/JS clients
        helper('cookie');
        $response = service('response');
        $response->setCookie('nm_token',   $token,        $this->expiry,        '', '/', '', false, true);
        $response->setCookie('nm_refresh', $refreshToken, $this->refreshExpiry, '', '/', '', false, true);
    }

    public function clearAuthCookies(): void
    {
        session()->remove('nm_token');
        session()->remove('nm_refresh');
        session()->remove('auth_user');

        helper('cookie');
        $response = service('response');
        $response->deleteCookie('nm_token');
        $response->deleteCookie('nm_refresh');
    }
}
