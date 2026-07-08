<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\ParticipantModel;
use CodeIgniter\HTTP\Response;

class ParticipantController extends BaseController
{
    private MeetingModel $meetingModel;
    private ParticipantModel $participantModel;

    public function __construct()
    {
        $this->meetingModel     = new MeetingModel();
        $this->participantModel = new ParticipantModel();
    }

    public function apiList(string $uuid): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }
        if (!$user || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);
        $perPage = max(1, min(50, $perPage));

        $participants = $this->participantModel->getByMeetingPaginated($meeting['meeting_id'], $page, $perPage);
        $pager        = $this->participantModel->pager;

        return $this->response->setJSON([
            'data'     => $participants,
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $pager->getTotal(),
            'has_more' => $page < $pager->getPageCount(),
        ]);
    }

    /** Self-service: the caller (registered user or guest) leaves the meeting. */
    public function apiLeave(string $uuid): mixed
    {
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $participant = $this->resolveActor($meeting['meeting_id']);
        if (!$participant) {
            return $this->response->setJSON(['error' => 'Not a participant of this meeting'])->setStatusCode(404);
        }

        $this->participantModel->update($participant['participant_id'], [
            'status'  => 'Left',
            'left_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['message' => 'Left meeting.']);
    }

    /** Host admits a single waiting participant, identified by participant_id/user_id/guest_id. */
    public function apiAdmit(string $uuid): mixed
    {
        $meeting = $this->requireHostMeeting($uuid);
        if ($meeting instanceof Response) return $meeting;

        $target = $this->resolveTarget($meeting['meeting_id'], $this->request->getJSON(true) ?? []);
        if (!$target) {
            return $this->response->setJSON(['error' => 'Participant not found'])->setStatusCode(404);
        }

        $this->participantModel->update($target['participant_id'], [
            'status'    => 'Admitted',
            'joined_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['message' => 'Participant admitted.']);
    }

    /** Host admits every participant currently in the waiting room. */
    public function apiAdmitAll(string $uuid): mixed
    {
        $meeting = $this->requireHostMeeting($uuid);
        if ($meeting instanceof Response) return $meeting;

        $count = $this->participantModel->admitAllWaiting($meeting['meeting_id']);
        return $this->response->setJSON(['message' => 'All waiting participants admitted.', 'count' => $count]);
    }

    /** Host permanently removes a participant. */
    public function apiRemove(string $uuid): mixed
    {
        $meeting = $this->requireHostMeeting($uuid);
        if ($meeting instanceof Response) return $meeting;

        $target = $this->resolveTarget($meeting['meeting_id'], $this->request->getJSON(true) ?? []);
        if (!$target) {
            return $this->response->setJSON(['error' => 'Participant not found'])->setStatusCode(404);
        }

        $this->participantModel->update($target['participant_id'], ['status' => 'Removed']);
        return $this->response->setJSON(['message' => 'Participant removed.']);
    }

    /** Host sends an admitted participant back to the waiting room (soft kick). */
    public function apiDropToWaiting(string $uuid): mixed
    {
        $meeting = $this->requireHostMeeting($uuid);
        if ($meeting instanceof Response) return $meeting;

        $target = $this->resolveTarget($meeting['meeting_id'], $this->request->getJSON(true) ?? []);
        if (!$target) {
            return $this->response->setJSON(['error' => 'Participant not found'])->setStatusCode(404);
        }

        $this->participantModel->update($target['participant_id'], ['status' => 'Waiting']);
        return $this->response->setJSON(['message' => 'Participant moved to waiting room.']);
    }

    /**
     * Sets a participant's mute state. Callable by the host (forcing any
     * participant) or by the participant themselves (reporting their own
     * mic toggle) — both cases update the same authoritative DB flag.
     */
    public function apiMute(string $uuid): mixed
    {
        return $this->setParticipantFlag($uuid, $this->request->getJSON(true) ?? [], 'is_muted');
    }

    /** Sets a participant's camera-off state. Same host-or-self rule as apiMute. */
    public function apiVideo(string $uuid): mixed
    {
        return $this->setParticipantFlag($uuid, $this->request->getJSON(true) ?? [], 'is_video_off');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function setParticipantFlag(string $uuid, array $body, string $field): mixed
    {
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $target = $this->resolveTarget($meeting['meeting_id'], $body);
        if (!$target) {
            return $this->response->setJSON(['error' => 'Participant not found'])->setStatusCode(404);
        }

        $actor  = $this->resolveActor($meeting['meeting_id']);
        $isSelf = $actor && $actor['participant_id'] === $target['participant_id'];

        if (!$this->isHost($meeting) && !$isSelf) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $value = array_key_exists($field, $body) ? (bool) $body[$field] : !$target[$field];

        $this->participantModel->update($target['participant_id'], [$field => $value]);
        return $this->response->setJSON(['message' => 'Updated.', $field => $value]);
    }

    private function requireHostMeeting(string $uuid): array|Response
    {
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }
        if (!$this->isHost($meeting)) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }
        return $meeting;
    }

    private function isHost(array $meeting): bool
    {
        $user = session()->get('auth_user');
        return $user && (int)$meeting['host_user_id'] === (int)$user['user_id'];
    }

    /** Resolves a target participant row from participant_id, user_id, or guest_id in the request body. */
    private function resolveTarget(int $meetingId, array $body): ?array
    {
        if (!empty($body['participant_id'])) {
            $row = $this->participantModel->find((int) $body['participant_id']);
            return ($row && (int)$row['meeting_id'] === $meetingId) ? $row : null;
        }
        if (!empty($body['user_id'])) {
            return $this->participantModel->findByMeetingAndUser($meetingId, (int) $body['user_id']);
        }
        if (!empty($body['guest_id'])) {
            return $this->participantModel->findByMeetingAndGuest($meetingId, (string) $body['guest_id']);
        }
        return null;
    }

    /** Resolves the requesting caller's own participant row from their session identity. */
    private function resolveActor(int $meetingId): ?array
    {
        $user = session()->get('auth_user');
        if ($user) {
            return $this->participantModel->findByMeetingAndUser($meetingId, (int) $user['user_id']);
        }
        $guest = session()->get('guest_user');
        if (!empty($guest['is_guest']) && !empty($guest['guest_id'])) {
            return $this->participantModel->findByMeetingAndGuest($meetingId, (string) $guest['guest_id']);
        }
        return null;
    }
}
