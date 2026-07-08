<?php

namespace App\Services;

use App\Models\MeetingModel;
use App\Models\ParticipantModel;

class MeetingService
{
    private MeetingModel $meetingModel;
    private ParticipantModel $participantModel;

    public function __construct()
    {
        $this->meetingModel     = new MeetingModel();
        $this->participantModel = new ParticipantModel();
    }

    public function generateUuid(): string
    {
        do {
            $id = (string) random_int(1000000000, 9999999999);
        } while ($this->meetingModel->where('meeting_uuid', $id)->countAllResults() > 0);

        return $id;
    }

    public function generateToken(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    public function createMeeting(int $hostId, array $data): array
    {
        $uuid  = $this->generateUuid();
        $token = $this->generateToken();

        $meetingData = [
            'meeting_uuid'    => $uuid,
            'meeting_token'   => $token,
            'host_user_id'    => $hostId,
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'scheduled_start' => $data['scheduled_start'],
            'scheduled_end'   => $data['scheduled_end'],
            'waiting_room'    => isset($data['waiting_room']) ? (int)$data['waiting_room'] : 1,
            'allow_recording' => isset($data['allow_recording']) ? (int)$data['allow_recording'] : 1,
            'max_participants' => $data['max_participants'] ?? 300,
            'status'          => 'Scheduled',
        ];

        if (!empty($data['password'])) {
            $meetingData['password'] = $data['password'];
        }

        $meetingId = $this->meetingModel->insert($meetingData);

        // Add host as participant
        $this->participantModel->insert([
            'meeting_id' => $meetingId,
            'user_id'    => $hostId,
            'role'       => 'Host',
            'status'     => 'Admitted',
            'joined_at'  => null,
        ]);

        return $this->meetingModel->find($meetingId);
    }

    public function startMeeting(int $meetingId): bool
    {
        return (bool) $this->meetingModel->update($meetingId, [
            'status'       => 'Active',
            'actual_start' => date('Y-m-d H:i:s'),
        ]);
    }

    public function endMeeting(int $meetingId): bool
    {
        return (bool) $this->meetingModel->update($meetingId, [
            'status'     => 'Ended',
            'actual_end' => date('Y-m-d H:i:s'),
        ]);
    }

    public function verifyPassword(array $meeting, ?string $password): bool
    {
        if (empty($meeting['password'])) {
            return true;
        }
        return ($password ?? '') === $meeting['password'];
    }

    public function joinMeeting(array $meeting, int $userId, ?string $guestName, ?string $password): array
    {
        if (!$this->verifyPassword($meeting, $password)) {
            return ['success' => false, 'error' => 'Invalid meeting password'];
        }

        // Check if already a participant
        $existing = $this->participantModel->findByMeetingAndUser($meeting['meeting_id'], $userId);
        if ($existing) {
            return ['success' => true, 'participant' => $existing, 'waiting' => $existing['status'] === 'Waiting'];
        }

        $status = $meeting['waiting_room'] ? 'Waiting' : 'Admitted';

        $participantId = $this->participantModel->insert([
            'meeting_id' => $meeting['meeting_id'],
            'user_id'    => $userId,
            'role'       => 'Attendee',
            'status'     => $status,
            'joined_at'  => $status === 'Admitted' ? date('Y-m-d H:i:s') : null,
        ]);

        $participant = $this->participantModel->find($participantId);
        return ['success' => true, 'participant' => $participant, 'waiting' => $status === 'Waiting'];
    }
}
