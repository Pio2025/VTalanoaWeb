<?php
namespace App\Controllers;

use App\Services\JWTService;

class WorkspaceController extends BaseController
{
    public function index(): string
    {
        $user = session('auth_user');
        $jwt  = (new JWTService())->getTokenFromRequest();

        return view('workspace/index', [
            'title'        => 'Workplace — VTalanoa',
            'user'         => $user,
            'token'        => $jwt,
            'signalingUrl' => env('SIGNALING_SERVER_URL', 'https://navuli-meet-signaling.onrender.com'),
        ]);
    }
}
