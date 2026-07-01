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
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $participants = $this->participantModel->getByMeeting($meeting['meeting_id']);
        return $this->response->setJSON(['data' => $participants]);
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
