<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\UserModel;
use App\Services\MeetingService;
use App\Services\EmailService;

class MeetingController extends BaseController
{
    private MeetingModel $meetingModel;
    private MeetingService $meetingService;

    public function __construct()
    {
        $this->meetingModel   = new MeetingModel();
        $this->meetingService = new MeetingService();
    }

    public function index(): string
    {
        $user     = session()->get('auth_user');
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $meetings = $this->meetingModel->getUserMeetings($user['user_id'], $page, 10);
        $pager    = $this->meetingModel->pager;

        return view('meetings/index', [
            'title'    => 'My Meetings — VTalanoa',
            'user'     => $user,
            'meetings' => $meetings,
            'pager'    => $pager,
        ]);
    }

    public function schedulePage(): string
    {
        $user = session()->get('auth_user');
        return view('meetings/schedule', [
            'title' => 'Schedule Meeting — VTalanoa',
            'user'  => $user,
        ]);
    }

    public function detail(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->getWithHost($token);

        if (!$meeting) {
            return redirect()->to(base_url('meetings'))->with('error', 'Meeting not found.');
        }

        return view('meetings/detail', [
            'title'   => esc($meeting['title']) . ' — VTalanoa',
            'user'    => $user,
            'meeting' => $meeting,
        ]);
    }

    public function editPage(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return redirect()->to(base_url('meetings'))->with('error', 'Not authorized.');
        }

        return view('meetings/edit', [
            'title'   => 'Edit Meeting — VTalanoa',
            'user'    => $user,
            'meeting' => $meeting,
        ]);
    }

    public function joinPage(string $token): mixed
    {
        // Support both meeting_token (UUID) and meeting_uuid (10-digit numeric ID)
        $meeting = $this->meetingModel->getWithHost($token);
        if (!$meeting) {
            $row = $this->meetingModel->findByUuid($token);
            if ($row) {
                return redirect()->to(base_url('join/' . $row['meeting_token']));
            }
        }

        if (!$meeting || $meeting['status'] === 'Cancelled') {
            return redirect()->to(base_url('auth/login'))->with('error', 'Meeting not found or cancelled.');
        }

        $user = session()->get('auth_user');
        return view('meetings/join', [
            'title'   => 'Join ' . esc($meeting['title']) . ' — VTalanoa',
            'meeting' => $meeting,
            'user'    => $user,
        ]);
    }

    public function roomPage(string $token): mixed
    {
        $user    = session()->get('auth_user') ?? session()->get('guest_user');
        $meeting = $this->meetingModel->getWithHost($token);

        if (!$meeting) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Meeting not found.');
        }

        $signalingUrl = env('SIGNALING_SERVER_URL', 'https://navuli-meet-signaling.onrender.com');
        $authToken    = session()->get('nm_token') ?? '';
        $iceServers   = $this->buildIceServers();
        $sfuProxyBase = base_url('sfu-proxy');

        return view('room/index', [
            'title'        => esc($meeting['title']) . ' — VTalanoa',
            'user'         => $user,
            'meeting'      => $meeting,
            'signalingUrl' => $signalingUrl,
            'token'        => $authToken,
            'iceServers'   => $iceServers,
            'sfuProxyBase' => $sfuProxyBase,
        ]);
    }

    private function buildIceServers(): array
    {
        // When using Cloudflare Realtime SFU every client connects to Cloudflare's
        // anycast edge, not to each other, so standard STUN is sufficient.
        // Cloudflare TURN is free when used with the SFU — add TURN credentials
        // here if you later create a TURN key via the CF dashboard.
        return [
            ['urls' => 'stun:stun.cloudflare.com:3478'],
            ['urls' => 'stun:stun1.l.google.com:19302'],
        ];
    }

    // ---- API ----

    public function apiList(): mixed
    {
        $user     = session()->get('auth_user');
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $meetings = $this->meetingModel->getUserMeetings($user['user_id'], $page, 10);
        return $this->response->setJSON(['data' => $meetings, 'page' => $page]);
    }

    public function apiCreate(): mixed
    {
        $user = session()->get('auth_user');
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $rules = [
            'title'           => 'required|max_length[200]',
            'scheduled_start' => 'required',
            'scheduled_end'   => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['errors' => $this->validator->getErrors()])->setStatusCode(422);
        }

        $meeting = $this->meetingService->createMeeting($user['user_id'], $data);
        $joinUrl = base_url('join/' . $meeting['meeting_token']);

        try {
            $hostName      = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
            $plainPassword = $data['password'] ?? '';
            (new EmailService())->sendMeetingCreated($user['email'], $hostName, $meeting, $joinUrl, $plainPassword);
        } catch (\Throwable $e) {
            log_message('error', '[MeetingController] Email on create failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'meeting_uuid'  => $meeting['meeting_uuid'],
            'meeting_token' => $meeting['meeting_token'],
            'join_url'      => $joinUrl,
            'status'        => $meeting['status'],
            'meeting'       => $meeting,
        ])->setStatusCode(201);
    }

    public function apiGet(string $token): mixed
    {
        $meeting = $this->meetingModel->getWithHost($token);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }
        return $this->response->setJSON(['data' => $meeting]);
    }

    public function apiUpdate(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $data    = $this->request->getJSON(true);
        $allowed = ['title', 'description', 'scheduled_start', 'scheduled_end', 'waiting_room', 'allow_recording', 'max_participants'];
        $update  = array_intersect_key($data, array_flip($allowed));

        $this->meetingModel->update($meeting['meeting_id'], $update);
        return $this->response->setJSON(['data' => $this->meetingModel->find($meeting['meeting_id'])]);
    }

    public function apiDelete(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $this->meetingModel->update($meeting['meeting_id'], ['status' => 'Cancelled']);
        return $this->response->setJSON(['message' => 'Meeting cancelled.']);
    }

    public function apiStart(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $this->meetingService->startMeeting($meeting['meeting_id']);
        return $this->response->setJSON(['message' => 'Meeting started.', 'room_url' => base_url('room/' . $token)]);
    }

    public function apiEnd(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $this->meetingService->endMeeting($meeting['meeting_id']);
        return $this->response->setJSON(['message' => 'Meeting ended.']);
    }

    public function apiJoin(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        // Backward compat: 10-digit numeric ID
        if (!$meeting) {
            $meeting = $this->meetingModel->findByUuid($token);
            if ($meeting) {
                $token = $meeting['meeting_token'];
            }
        }

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found'])->setStatusCode(404);
        }

        if ($meeting['status'] === 'Ended' || $meeting['status'] === 'Cancelled') {
            return $this->response->setJSON(['error' => 'Meeting is no longer available'])->setStatusCode(410);
        }

        $data     = $this->request->getJSON(true);
        $password = $data['password'] ?? null;

        // ── Guest join (no session) ──────────────────────────────
        if (!$user) {
            $guestName = trim($data['display_name'] ?? '');
            if (!$guestName) {
                return $this->response->setJSON(['error' => 'Please enter a display name to join as guest.'])->setStatusCode(422);
            }

            if (!$this->meetingService->verifyPassword($meeting, $password)) {
                return $this->response->setJSON(['error' => 'Invalid meeting password'])->setStatusCode(403);
            }

            $guestId   = 'guest_' . bin2hex(random_bytes(8));
            $guestUser = [
                'user_id'       => 0,
                'guest_id'      => $guestId,
                'fname'         => $guestName,
                'lname'         => '',
                'email'         => '',
                'username'      => 'guest',
                'user_status'   => 'Active',
                'profile_photo' => null,
                'is_guest'      => true,
            ];
            session()->set('guest_user', $guestUser);

            $jwtService = new \App\Services\JWTService();
            $guestToken = $jwtService->generateToken([
                'user_id'    => 0,
                'guest_id'   => $guestId,
                'guest_name' => $guestName,
                'is_guest'   => true,
            ]);
            session()->set('nm_token', $guestToken);

            return $this->response->setJSON([
                'waiting'  => (bool) $meeting['waiting_room'],
                'room_url' => base_url('room/' . $token),
                'token'    => $guestToken,
            ]);
        }

        // ── Authenticated join ───────────────────────────────────
        $result = $this->meetingService->joinMeeting($meeting, $user['user_id'], null, $password);

        if (!$result['success']) {
            return $this->response->setJSON(['error' => $result['error']])->setStatusCode(403);
        }

        $authToken = session()->get('nm_token') ?? '';
        return $this->response->setJSON([
            'waiting'  => $result['waiting'],
            'room_url' => base_url('room/' . $token),
            'token'    => $authToken,
        ]);
    }

    public function apiResolve(string $id): mixed
    {
        // Look up by 10-digit meeting ID (meeting_uuid) or by token
        $meeting = $this->meetingModel->findByUuid($id);
        if (!$meeting) {
            $meeting = $this->meetingModel->findByToken($id);
        }

        if (!$meeting || $meeting['status'] === 'Cancelled') {
            return $this->response->setJSON(['error' => 'Meeting not found'])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'token'    => $meeting['meeting_token'],
            'join_url' => base_url('join/' . $meeting['meeting_token']),
        ]);
    }
}
