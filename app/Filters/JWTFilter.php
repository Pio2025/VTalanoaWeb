<?php

namespace App\Filters;

use App\Services\JWTService;
use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JWTFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        // 1a. Registered user session
        $sessionUser = session()->get('auth_user');
        if (!empty($sessionUser['user_id']) && ($sessionUser['user_status'] ?? '') === 'Active') {
            return null;
        }

        // 1b. Guest session (set during guest join)
        $guestUser = session()->get('guest_user');
        if (!empty($guestUser['is_guest'])) {
            return null;
        }

        // 2. JWT token fallback (API clients, mobile, or session-less requests)
        $jwtService = new JWTService();
        $token      = $jwtService->getTokenFromRequest();

        if (!$token) {
            return $this->unauthorized($request);
        }

        $decoded = $jwtService->decode($token);
        if (!$decoded) {
            return $this->unauthorized($request);
        }

        $userModel = new UserModel();
        $user      = $userModel->find($decoded->user_id ?? 0);

        if (!$user || $user['user_status'] !== 'Active') {
            return $this->unauthorized($request);
        }

        session()->set('auth_user', $user);
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }

    private function unauthorized(RequestInterface $request): mixed
    {
        $path  = $request->getUri()->getPath();
        $isApi = str_starts_with($path, 'api/') || str_contains($path, '/api/');

        if ($isApi) {
            return response()
                ->setJSON(['error' => 'Unauthorized', 'message' => 'Authentication required'])
                ->setStatusCode(401);
        }

        return redirect()->to(base_url('auth/login'))->with('error', 'Please log in to continue.');
    }
}
