<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\SocialLoginModel;
use App\Services\JWTService;
use League\OAuth2\Client\Provider\Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

class SocialAuthController extends BaseController
{
    private UserModel $userModel;
    private SocialLoginModel $socialModel;
    private JWTService $jwtService;

    public function __construct()
    {
        $this->userModel   = new UserModel();
        $this->socialModel = new SocialLoginModel();
        $this->jwtService  = new JWTService();
    }

    public function redirect(string $provider): mixed
    {
        $oauth = $this->getProvider($provider);
        if (!$oauth) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Unknown provider.');
        }

        $authUrl = $oauth->getAuthorizationUrl();
        session()->set('oauth2state_' . $provider, $oauth->getState());
        return redirect()->to($authUrl);
    }

    public function callback(string $provider): mixed
    {
        $oauth = $this->getProvider($provider);
        if (!$oauth) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Unknown provider.');
        }

        $state = $this->request->getGet('state') ?? $this->request->getPost('state');
        $code  = $this->request->getGet('code') ?? $this->request->getPost('code');

        $storedState = session()->get('oauth2state_' . $provider);
        if (!$state || $state !== $storedState) {
            session()->remove('oauth2state_' . $provider);
            return redirect()->to(base_url('auth/login'))->with('error', 'Invalid state. Please try again.');
        }

        try {
            $accessToken  = $oauth->getAccessToken('authorization_code', ['code' => $code]);
            $ownerDetails = $oauth->getResourceOwner($accessToken);
            $ownerArray   = $ownerDetails->toArray();

            $providerUserId = (string) ($ownerArray['id'] ?? $ownerArray['sub'] ?? '');
            $providerEmail  = $ownerArray['email'] ?? null;
            $firstName      = $ownerArray['given_name'] ?? $ownerArray['name'] ?? 'User';
            $lastName       = $ownerArray['family_name'] ?? '';

            if (!$providerUserId) {
                return redirect()->to(base_url('auth/login'))->with('error', 'Could not retrieve profile from provider.');
            }

            // Check if social login already exists
            $socialLogin = $this->socialModel->findByProvider($provider, $providerUserId);

            if ($socialLogin) {
                // Update tokens and log in
                $this->socialModel->update($socialLogin['social_id'], [
                    'access_token'     => $accessToken->getToken(),
                    'refresh_token'    => $accessToken->getRefreshToken(),
                    'token_expires_at' => $accessToken->getExpires() ? date('Y-m-d H:i:s', $accessToken->getExpires()) : null,
                ]);
                $user = $this->userModel->find($socialLogin['user_id']);
            } elseif ($providerEmail) {
                // Check if email matches existing user
                $existingUser = $this->userModel->findByEmail($providerEmail);

                if ($existingUser) {
                    // Link to existing account
                    $user = $existingUser;
                    $this->updateAuthType($user['user_id'], 'both');
                } else {
                    // Create new user
                    $user = $this->createSocialUser($firstName, $lastName, $providerEmail);
                }

                $this->socialModel->insert([
                    'user_id'          => $user['user_id'],
                    'provider'         => $provider,
                    'provider_user_id' => $providerUserId,
                    'provider_email'   => $providerEmail,
                    'access_token'     => $accessToken->getToken(),
                    'refresh_token'    => $accessToken->getRefreshToken(),
                    'token_expires_at' => $accessToken->getExpires() ? date('Y-m-d H:i:s', $accessToken->getExpires()) : null,
                    'linked_at'        => date('Y-m-d H:i:s'),
                ]);
            } else {
                return redirect()->to(base_url('auth/login'))->with('error', 'No email provided by ' . ucfirst($provider) . '. Please use email login.');
            }

            if (!$user || $user['user_status'] !== 'Active') {
                return redirect()->to(base_url('auth/login'))->with('error', 'Account suspended.');
            }

            // Create session
            $payload = ['user_id' => $user['user_id'], 'email' => $user['email'], 'uuid' => $user['uuid']];
            $token   = $this->jwtService->generateToken($payload);
            $refresh = $this->jwtService->generateRefreshToken($payload);
            $this->jwtService->setAuthCookies($token, $refresh);
            session()->set('auth_user', $user);

            return redirect()->to(base_url('dashboard'))->with('success', 'Welcome back!');

        } catch (\Exception $e) {
            log_message('error', 'OAuth callback error: ' . $e->getMessage());
            return redirect()->to(base_url('auth/login'))->with('error', 'Authentication failed. Please try again.');
        }
    }

    public function link(string $provider): mixed
    {
        return redirect()->to(base_url('auth/social/' . $provider));
    }

    public function unlink(string $provider): mixed
    {
        $user    = session()->get('auth_user');
        $socials = $this->socialModel->getByUser($user['user_id']);

        if (count($socials) <= 1 && empty($user['password'])) {
            return $this->response->setJSON(['error' => 'Cannot unlink your only login method.'])->setStatusCode(400);
        }

        $this->socialModel->unlinkProvider($user['user_id'], $provider);
        return $this->response->setJSON(['message' => ucfirst($provider) . ' unlinked.']);
    }

    private function getProvider(string $provider): mixed
    {
        $base = env('OAUTH_REDIRECT_BASE', base_url());

        return match ($provider) {
            'google' => new Google([
                'clientId'     => env('GOOGLE_CLIENT_ID'),
                'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
                'redirectUri'  => $base . '/auth/callback/google',
            ]),
            'microsoft' => new Microsoft([
                'clientId'     => env('MICROSOFT_CLIENT_ID'),
                'clientSecret' => env('MICROSOFT_CLIENT_SECRET'),
                'redirectUri'  => $base . '/auth/callback/microsoft',
            ]),
            default => null,
        };
    }

    private function createSocialUser(string $fname, string $lname, string $email): array
    {
        $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', $fname . $lname));
        $username     = $baseUsername ?: 'user';
        $counter      = 1;

        while ($this->userModel->findByUsername($username)) {
            $username = $baseUsername . $counter++;
        }

        $userId = $this->userModel->insert([
            'uuid'      => $this->generateUuid(),
            'fname'     => $fname,
            'lname'     => $lname,
            'email'     => strtolower($email),
            'username'  => $username,
            'password'  => null,
            'auth_type' => 'social',
        ]);

        return $this->userModel->find($userId);
    }

    private function updateAuthType(int $userId, string $type): void
    {
        $this->userModel->update($userId, ['auth_type' => $type]);
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
