<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\ParticipantModel;

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

    public function apiLeave(string $uuid): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting || !$user) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $participant = $this->participantModel->findByMeetingAndUser($meeting['meeting_id'], (int) $user['user_id']);
        if (!$participant) {
            return $this->response->setJSON(['error' => 'Not a participant of this meeting'])->setStatusCode(404);
        }

        $this->participantModel->update($participant['participant_id'], [
            'status'  => 'Left',
            'left_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['message' => 'Left meeting.']);
    }

    public function apiAdmit(string $uuid, int $participantId): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $this->participantModel->update($participantId, [
            'status'    => 'Admitted',
            'joined_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['message' => 'Participant admitted.']);
    }

    public function apiRemove(string $uuid, int $participantId): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $this->participantModel->update($participantId, ['status' => 'Removed']);
        return $this->response->setJSON(['message' => 'Participant removed.']);
    }

    public function apiMute(string $uuid, int $participantId): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $participant = $this->participantModel->find($participantId);
        $this->participantModel->update($participantId, ['is_muted' => $participant ? !$participant['is_muted'] : 1]);
        return $this->response->setJSON(['message' => 'Mute status toggled.']);
    }
}
