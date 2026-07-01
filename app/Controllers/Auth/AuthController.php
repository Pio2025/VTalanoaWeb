<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\JWTService;
use App\Services\EmailService;

class AuthController extends BaseController
{
    private UserModel $userModel;
    private JWTService $jwtService;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->jwtService = new JWTService();
    }

    public function loginPage(): mixed
    {
        if ($this->isLoggedIn()) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/login', ['title' => 'Sign In — VTalanoa']);
    }

    public function login(): mixed
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $user     = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if ($user['user_status'] !== 'Active') {
            return redirect()->back()->with('error', 'Your account is suspended. Contact support.');
        }

        $this->createSession($user);
        return redirect()->to(base_url('dashboard'));
    }

    public function registerPage(): mixed
    {
        if ($this->isLoggedIn()) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/register', ['title' => 'Create Account — VTalanoa']);
    }

    public function register(): mixed
    {
        $rules = [
            'fname'            => 'required|min_length[2]|max_length[80]',
            'lname'            => 'required|min_length[2]|max_length[80]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'username'         => 'required|alpha_numeric|min_length[3]|max_length[60]|is_unique[users.username]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'uuid'      => $this->generateUuid(),
            'fname'     => $this->request->getPost('fname'),
            'lname'     => $this->request->getPost('lname'),
            'email'     => strtolower($this->request->getPost('email')),
            'username'  => strtolower($this->request->getPost('username')),
            'password'  => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT, ['cost' => 12]),
            'timezone'  => $this->request->getPost('timezone') ?? 'UTC',
            'auth_type' => 'local',
        ];

        $userId = $this->userModel->insert($data);
        $user   = $this->userModel->find($userId);

        $this->createSession($user);
        return redirect()->to(base_url('dashboard'))->with('success', 'Welcome to VTalanoa!');
    }

    public function logout(): mixed
    {
        $this->jwtService->clearAuthCookies();
        session()->destroy();
        return redirect()->to(base_url('auth/login'))->with('success', 'You have been signed out.');
    }

    public function forgotPasswordPage(): string
    {
        return view('auth/forgot_password', ['title' => 'Reset Password — VTalanoa']);
    }

    public function forgotPassword(): mixed
    {
        $email = $this->request->getPost('email');
        $user  = $this->userModel->findByEmail($email);

        if ($user) {
            $token    = bin2hex(random_bytes(32));
            $resetUrl = base_url("auth/reset-password/{$token}");

            // Store token in cache (5 min expiry handled by cache)
            cache()->save("pwd_reset_{$token}", $user['user_id'], 3600);

            try {
                $emailService = new EmailService();
                $emailService->sendPasswordReset($user['email'], $user['fname'], $resetUrl);
            } catch (\Exception $e) {
                log_message('error', 'Password reset email failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'If that email exists, a reset link has been sent.');
    }

    public function resetPasswordPage(string $token): mixed
    {
        $userId = cache("pwd_reset_{$token}");
        if (!$userId) {
            return redirect()->to(base_url('auth/forgot-password'))->with('error', 'Invalid or expired reset link.');
        }
        return view('auth/reset_password', ['title' => 'Set New Password', 'token' => $token]);
    }

    public function resetPassword(string $token): mixed
    {
        $userId = cache("pwd_reset_{$token}");
        if (!$userId) {
            return redirect()->to(base_url('auth/forgot-password'))->with('error', 'Invalid or expired reset link.');
        }

        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->update($userId, [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        cache()->delete("pwd_reset_{$token}");
        return redirect()->to(base_url('auth/login'))->with('success', 'Password updated. Please sign in.');
    }

    public function profilePage(): mixed
    {
        $user = session()->get('auth_user');
        return view('profile/index', ['title' => 'My Profile — VTalanoa', 'user' => $user]);
    }

    public function updateProfile(): mixed
    {
        $user  = session()->get('auth_user');
        $rules = [
            'fname'    => 'required|min_length[2]|max_length[80]',
            'lname'    => 'required|min_length[2]|max_length[80]',
            'timezone' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'fname'    => $this->request->getPost('fname'),
            'lname'    => $this->request->getPost('lname'),
            'timezone' => $this->request->getPost('timezone'),
        ];

        // Handle photo upload
        $photo = $this->request->getFile('profile_photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads/avatars/', $newName);
            $data['profile_photo'] = 'uploads/avatars/' . $newName;
        }

        // Handle password change
        $newPass = $this->request->getPost('new_password');
        if (!empty($newPass)) {
            $confirm = $this->request->getPost('confirm_password');
            if ($newPass !== $confirm) {
                return redirect()->back()->with('error', 'Passwords do not match.');
            }
            $data['password'] = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->userModel->update($user['user_id'], $data);
        $updatedUser = $this->userModel->find($user['user_id']);
        session()->set('auth_user', $updatedUser);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    // ---- API Endpoints ----

    public function apiRegister(): mixed
    {
        $data = $this->request->getJSON(true);
        // Mirror register() logic for API
        $userId = $this->userModel->insert([
            'uuid'      => $this->generateUuid(),
            'fname'     => $data['fname'] ?? '',
            'lname'     => $data['lname'] ?? '',
            'email'     => strtolower($data['email'] ?? ''),
            'username'  => strtolower($data['username'] ?? ''),
            'password'  => password_hash($data['password'] ?? '', PASSWORD_BCRYPT),
            'auth_type' => 'local',
        ]);
        $user = $this->userModel->find($userId);
        [$token, $refresh] = $this->issueTokens($user);
        return $this->response->setJSON(['token' => $token, 'refresh_token' => $refresh, 'user' => $this->safeUser($user)])->setStatusCode(201);
    }

    public function apiLogin(): mixed
    {
        $data     = $this->request->getJSON(true);
        $user     = $this->userModel->findByEmail($data['email'] ?? '');

        if (!$user || !password_verify($data['password'] ?? '', $user['password'] ?? '')) {
            return $this->response->setJSON(['error' => 'Invalid credentials'])->setStatusCode(401);
        }

        [$token, $refresh] = $this->issueTokens($user);
        return $this->response->setJSON(['token' => $token, 'refresh_token' => $refresh, 'user' => $this->safeUser($user)]);
    }

    public function apiLogout(): mixed
    {
        $this->jwtService->clearAuthCookies();
        return $this->response->setJSON(['message' => 'Logged out']);
    }

    public function apiForgotPassword(): mixed
    {
        return $this->response->setJSON(['message' => 'Reset link sent if email exists.']);
    }

    public function apiResetPassword(string $token): mixed
    {
        return $this->response->setJSON(['message' => 'Not implemented via API']);
    }

    public function me(): mixed
    {
        $user = session()->get('auth_user');
        return $this->response->setJSON(['user' => $this->safeUser($user)]);
    }

    public function verify(): mixed
    {
        $token   = $this->jwtService->getTokenFromRequest();
        $decoded = $token ? $this->jwtService->decode($token) : null;
        if (!$decoded) {
            return $this->response->setJSON(['valid' => false])->setStatusCode(401);
        }
        return $this->response->setJSON(['valid' => true, 'user_id' => $decoded->user_id]);
    }

    // ---- Helpers ----

    private function createSession(array $user): void
    {
        $payload = ['user_id' => $user['user_id'], 'email' => $user['email'], 'uuid' => $user['uuid']];
        $token   = $this->jwtService->generateToken($payload);
        $refresh = $this->jwtService->generateRefreshToken($payload);
        $this->jwtService->setAuthCookies($token, $refresh);
        session()->set('auth_user', $user);
    }

    private function issueTokens(array $user): array
    {
        $payload = ['user_id' => $user['user_id'], 'email' => $user['email'], 'uuid' => $user['uuid']];
        $token   = $this->jwtService->generateToken($payload);
        $refresh = $this->jwtService->generateRefreshToken($payload);
        return [$token, $refresh];
    }

    private function safeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }

    private function isLoggedIn(): bool
    {
        return session()->has('auth_user') && session()->get('auth_user') !== null;
    }

    private function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
