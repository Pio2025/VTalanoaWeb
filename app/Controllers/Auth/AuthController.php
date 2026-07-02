<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PasswordResetModel;
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
        $rules = ['email' => 'required|valid_email'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim($this->request->getPost('email')));

        // Verify the email belongs to a registered, active account
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return redirect()->back()->withInput()
                             ->with('error', 'No account found with that email address. Please check and try again.');
        }
        if ($user['user_status'] !== 'Active') {
            return redirect()->back()->withInput()
                             ->with('error', 'This account is suspended. Please contact support.');
        }

        $passwordResetModel = new PasswordResetModel();
        $passwordResetModel->purgeExpired();

        // Cooldown: if a link was sent in the last 2 minutes show the confirmation
        // page without resending (prevents accidental spam)
        if ($passwordResetModel->isRecentlySent($email)) {
            return view('auth/email_sent', ['title' => 'Check Your Inbox — VTalanoa', 'email' => $email]);
        }

        // Upsert token — update existing row or create a new one
        $plainToken = $passwordResetModel->upsertToken($email);
        $resetUrl   = base_url("auth/reset-password/{$plainToken}");

        // Send email — show a clear error if delivery fails
        $sent = (new EmailService())->sendPasswordReset($user['email'], $user['fname'], $resetUrl);
        if (!$sent) {
            return redirect()->back()->withInput()
                             ->with('error', 'We could not send the reset email. Please try again in a few minutes or contact support.');
        }

        return view('auth/email_sent', ['title' => 'Check Your Inbox — VTalanoa', 'email' => $email]);
    }

    public function resetPasswordPage(string $token): mixed
    {
        $passwordResetModel = new PasswordResetModel();
        $record = $passwordResetModel->findValidByToken($token);

        if (!$record) {
            return redirect()->to(base_url('auth/forgot-password'))
                             ->with('error', 'This reset link has expired or is invalid. Please request a new one.');
        }

        return view('auth/reset_password', ['title' => 'Set New Password — VTalanoa', 'token' => $token]);
    }

    public function resetPassword(string $token): mixed
    {
        $passwordResetModel = new PasswordResetModel();
        $record = $passwordResetModel->findValidByToken($token);

        if (!$record) {
            return redirect()->to(base_url('auth/forgot-password'))
                             ->with('error', 'This reset link has expired or is invalid. Please request a new one.');
        }

        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->findByEmail($record['email']);
        if (!$user) {
            return redirect()->to(base_url('auth/forgot-password'))->with('error', 'Account not found.');
        }

        $this->userModel->update($user['user_id'], [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        $passwordResetModel->deleteByToken($token);

        return redirect()->to(base_url('auth/login'))
                         ->with('success', 'Your password has been updated. Please sign in.');
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
        $data  = $this->request->getJSON(true);
        $email = strtolower(trim($data['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['message' => 'Invalid email address.'])->setStatusCode(422);
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || $user['user_status'] !== 'Active') {
            return $this->response->setJSON(['error' => 'No active account found with that email.'])->setStatusCode(404);
        }

        $passwordResetModel = new PasswordResetModel();
        $passwordResetModel->purgeExpired();

        if (!$passwordResetModel->isRecentlySent($email)) {
            $plainToken = $passwordResetModel->upsertToken($email);
            $resetUrl   = base_url("auth/reset-password/{$plainToken}");
            $sent = (new EmailService())->sendPasswordReset($user['email'], $user['fname'], $resetUrl);
            if (!$sent) {
                return $this->response->setJSON(['error' => 'Failed to send reset email. Please try again.'])->setStatusCode(500);
            }
        }

        return $this->response->setJSON(['message' => 'A password reset link has been sent to your email.']);
    }

    public function apiResetPassword(string $token): mixed
    {
        $data               = $this->request->getJSON(true);
        $password           = $data['password'] ?? '';
        $passwordConfirm    = $data['password_confirm'] ?? '';
        $passwordResetModel = new PasswordResetModel();
        $record             = $passwordResetModel->findValidByToken($token);

        if (!$record) {
            return $this->response->setJSON(['error' => 'Invalid or expired token.'])->setStatusCode(400);
        }
        if (strlen($password) < 8) {
            return $this->response->setJSON(['error' => 'Password must be at least 8 characters.'])->setStatusCode(422);
        }
        if ($password !== $passwordConfirm) {
            return $this->response->setJSON(['error' => 'Passwords do not match.'])->setStatusCode(422);
        }

        $user = $this->userModel->findByEmail($record['email']);
        if (!$user) {
            return $this->response->setJSON(['error' => 'Account not found.'])->setStatusCode(404);
        }

        $this->userModel->update($user['user_id'], [
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
        $passwordResetModel->deleteByToken($token);

        return $this->response->setJSON(['message' => 'Password updated successfully.']);
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
