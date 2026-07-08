<?php

namespace App\Filters;

use App\Services\JWTService;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Like JWTFilter, but never blocks the request. Populates session()
 * ->get('auth_user') / ('guest_user') when a valid session or JWT is
 * present; otherwise lets the request through unauthenticated so the
 * controller can handle its own guest path (e.g. meeting join, where a
 * first-time visitor has no session or JWT yet).
 */
class OptionalAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $sessionUser = session()->get('auth_user');
        if (!empty($sessionUser['user_id']) && ($sessionUser['user_status'] ?? '') === 'Active') {
            return null;
        }

        $guestUser = session()->get('guest_user');
        if (!empty($guestUser['is_guest'])) {
            return null;
        }

        $jwtService = new JWTService();
        $token      = $jwtService->getTokenFromRequest();
        if (!$token) {
            return null;
        }

        $decoded = $jwtService->decode($token);
        if (!$decoded) {
            return null;
        }

        if (!empty($decoded->is_guest)) {
            session()->set('guest_user', [
                'is_guest'   => true,
                'guest_id'   => $decoded->guest_id   ?? null,
                'guest_name' => $decoded->guest_name ?? null,
            ]);
            return null;
        }

        $userModel = new UserModel();
        $user      = $userModel->find($decoded->user_id ?? 0);
        if ($user && $user['user_status'] === 'Active') {
            session()->set('auth_user', $user);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
