<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Services\AnalyticsService;
use App\Services\EmailService;
use App\Services\JWTService;
use App\Services\MeetingService;

class MeetingController extends BaseController
{
    private MeetingModel $meetingModel;
    private MeetingService $meetingService;
    private AnalyticsService $analyticsService;

    public function __construct()
    {
        $this->meetingModel     = new MeetingModel();
        $this->meetingService   = new MeetingService();
        $this->analyticsService = new AnalyticsService();
    }

    // ── Routes ────────────────────────────────────────────────────────────────

    /** GET /api/meetings */
    public function index(): mixed
    {
        $user    = session()->get('auth_user');
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $perPage = max(1, min(50, $perPage));

        $meetings = $this->meetingModel->getUserMeetings($user['user_id'], $page, $perPage);
        $pager    = $this->meetingModel->pager;

        return $this->response->setJSON([
            'data'     => $meetings,
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $pager->getTotal(),
            'has_more' => $page < $pager->getPageCount(),
        ]);
    }

    /** POST /api/meetings */
    public function create(): mixed
    {
        $user = session()->get('auth_user');
        $data = $this->request->getJSON(true) ?? [];

        $errors = [];
        if (empty($data['title']))           $errors['title']           = 'Title is required.';
        if (empty($data['scheduled_start'])) $errors['scheduled_start'] = 'scheduled_start is required.';
        if (empty($data['scheduled_end']))   $errors['scheduled_end']   = 'scheduled_end is required.';

        if ($errors) {
            return $this->response->setJSON(['error' => 'Validation failed.', 'details' => $errors])->setStatusCode(422);
        }

        $meeting = $this->meetingService->createMeeting($user['user_id'], $data);
        $joinUrl = base_url('join/' . $meeting['meeting_token']);

        try {
            $hostName = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
            (new EmailService())->sendMeetingCreated($user['email'], $hostName, $meeting, $joinUrl, $data['password'] ?? '');
        } catch (\Throwable $e) {
            log_message('error', '[Api\MeetingController] Email on create failed: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'meeting'       => $meeting,
            'meeting_token' => $meeting['meeting_token'],
            'meeting_uuid'  => $meeting['meeting_uuid'],
            'join_url'      => $joinUrl,
        ])->setStatusCode(201);
    }

    /** GET /api/meetings/resolve/:id — look up by 10-digit UUID or meeting token */
    public function resolve(string $id): mixed
    {
        $meeting = $this->meetingModel->findByUuid($id) ?? $this->meetingModel->findByToken($id);

        if (!$meeting || $meeting['status'] === 'Cancelled') {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'token'             => $meeting['meeting_token'],
            'join_url'          => base_url('join/' . $meeting['meeting_token']),
            'password_required' => !empty($meeting['password']),
        ]);
    }

    /** GET /api/meetings/:token */
    public function show(string $token): mixed
    {
        $meeting = $this->meetingModel->getWithHost($token);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }

        // Only the host may see the meeting's real plaintext password —
        // everyone else (any other authenticated participant) only learns
        // whether one is required, mirroring resolve()'s shape.
        $user   = session()->get('auth_user');
        $isHost = $user && (int)$meeting['host_user_id'] === (int)$user['user_id'];
        if (!$isHost) {
            $meeting['password_required'] = !empty($meeting['password']);
            unset($meeting['password']);
        }

        return $this->response->setJSON(['data' => $meeting]);
    }

    /** GET /api/meetings/:token/stats */
    public function stats(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }
        if ((int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Only the host can view meeting stats.'])->setStatusCode(403);
        }

        return $this->response->setJSON(['data' => $this->analyticsService->getStats($meeting)]);
    }

    /** PUT /api/meetings/:token */
    public function update(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }
        if ((int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Only the host can edit this meeting.'])->setStatusCode(403);
        }

        $data    = $this->request->getJSON(true) ?? [];
        $allowed = ['title', 'description', 'scheduled_start', 'scheduled_end', 'waiting_room', 'allow_recording', 'max_participants'];
        $update  = array_intersect_key($data, array_flip($allowed));

        $this->meetingModel->update($meeting['meeting_id'], $update);
        return $this->response->setJSON(['data' => $this->meetingModel->find($meeting['meeting_id'])]);
    }

    /** DELETE /api/meetings/:token */
    public function destroy(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }
        if ((int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden. Only the host can cancel this meeting.'])->setStatusCode(403);
        }

        $this->meetingModel->update($meeting['meeting_id'], ['status' => 'Cancelled']);
        return $this->response->setJSON(['message' => 'Meeting cancelled.']);
    }

    /** POST /api/meetings/:token/start */
    public function start(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }
        if ((int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Only the host can start this meeting.'])->setStatusCode(403);
        }
        if ($meeting['status'] === 'Active') {
            return $this->response->setJSON(['message' => 'Meeting is already active.', 'meeting_token' => $token, 'room_url' => base_url('room/' . $token)]);
        }

        $this->meetingService->startMeeting($meeting['meeting_id']);
        return $this->response->setJSON(['message' => 'Meeting started.', 'meeting_token' => $token, 'room_url' => base_url('room/' . $token)]);
    }

    /** POST /api/meetings/:token/end */
    public function end(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }
        if ((int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Only the host can end this meeting.'])->setStatusCode(403);
        }

        $this->meetingService->endMeeting($meeting['meeting_id']);
        return $this->response->setJSON(['message' => 'Meeting ended.']);
    }

    /**
     * POST /api/meetings/:token/join
     *
     * Returns the room JWT (used for signaling server auth), ice_servers config,
     * and whether the user is in the waiting room. Flutter passes this token to
     * the Socket.IO signaling server on connect.
     */
    public function join(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        // Backward compat: numeric meeting_uuid
        if (!$meeting) {
            $meeting = $this->meetingModel->findByUuid($token);
            if ($meeting) {
                $token = $meeting['meeting_token'];
            }
        }

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Meeting not found.'])->setStatusCode(404);
        }

        if (in_array($meeting['status'], ['Ended', 'Cancelled'], true)) {
            return $this->response->setJSON(['error' => 'This meeting is no longer available.'])->setStatusCode(410);
        }

        $data     = $this->request->getJSON(true) ?? [];
        $password = $data['password'] ?? null;
        $jwtSvc   = new JWTService();

        // ── Guest join ───────────────────────────────────────────────────────
        if (!$user) {
            $guestName = trim($data['display_name'] ?? '');
            if (!$guestName) {
                return $this->response->setJSON(['error' => 'display_name is required for guest join.'])->setStatusCode(422);
            }
            $guestId = 'guest_' . bin2hex(random_bytes(8));
            $result  = $this->meetingService->joinAsGuest($meeting, $guestId, $guestName, $password);
            if (!$result['success']) {
                return $this->response->setJSON(['error' => 'Invalid meeting password.'])->setStatusCode(403);
            }

            $roomToken = $jwtSvc->generateToken([
                'user_id'    => 0,
                'guest_id'   => $guestId,
                'guest_name' => $guestName,
                'is_guest'   => true,
            ]);

            // Persist the guest identity + room token into the session. The
            // browser's join page does a full-page navigation to /room/:token
            // right after this (window.location.href = room_url), which can't
            // carry an Authorization header — without this, the strict `jwt`
            // filter on that route has nothing to authenticate the guest with
            // and bounces them to /auth/login. Session state survives the
            // navigation and satisfies both the filter and roomPage()'s own
            // guest_user/nm_token lookups. Mobile/API clients are unaffected —
            // they use the returned JSON token directly, not the session.
            // Shape matches what app/Views/room/index.php expects of $user
            // (fname/lname/profile_photo etc.), not just is_guest/guest_id.
            session()->set('guest_user', [
                'user_id'       => 0,
                'guest_id'      => $guestId,
                'fname'         => $guestName,
                'lname'         => '',
                'email'         => '',
                'username'      => 'guest',
                'user_status'   => 'Active',
                'profile_photo' => null,
                'is_guest'      => true,
            ]);
            session()->set('nm_token', $roomToken);

            return $this->response->setJSON([
                'waiting'        => (bool) $meeting['waiting_room'],
                'token'          => $roomToken,
                'meeting_token'  => $token,
                'room_url'       => base_url('room/' . $token),
                'ice_servers'    => $this->iceServers(),
                'participant_id' => $result['participant']['participant_id'] ?? null,
            ]);
        }

        // ── Authenticated join ───────────────────────────────────────────────
        $result = $this->meetingService->joinMeeting($meeting, (int)$user['user_id'], null, $password);
        if (!$result['success']) {
            return $this->response->setJSON(['error' => $result['error']])->setStatusCode(403);
        }

        // Issue a fresh room token so the signaling server can verify the caller
        $roomToken = $jwtSvc->generateToken([
            'user_id' => $user['user_id'],
            'email'   => $user['email'],
            'uuid'    => $user['uuid'] ?? null,
        ]);

        return $this->response->setJSON([
            'waiting'        => $result['waiting'],
            'token'          => $roomToken,
            'meeting_token'  => $token,
            'room_url'       => base_url('room/' . $token),
            'ice_servers'    => $this->iceServers(),
            'participant_id' => $result['participant']['participant_id'] ?? null,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function iceServers(): array
    {
        return [
            ['urls' => 'stun:stun.cloudflare.com:3478'],
            ['urls' => 'stun:stun1.l.google.com:19302'],
        ];
    }
}
