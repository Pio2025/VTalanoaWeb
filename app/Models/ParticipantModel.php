<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table      = 'meeting_participants';
    protected $primaryKey = 'participant_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'meeting_id', 'user_id', 'guest_name', 'guest_email',
        'role', 'status', 'joined_at', 'left_at', 'is_muted', 'is_video_off',
    ];

    protected $useTimestamps = true;

    public function getByMeeting(int $meetingId): array
    {
        return $this->select('meeting_participants.*, users.fname, users.lname, users.profile_photo, users.email')
                    ->join('users', 'users.user_id = meeting_participants.user_id', 'left')
                    ->where('meeting_participants.meeting_id', $meetingId)
                    ->findAll();
    }

    public function getAdmitted(int $meetingId): array
    {
        return $this->select('meeting_participants.*, users.fname, users.lname, users.profile_photo')
                    ->join('users', 'users.user_id = meeting_participants.user_id', 'left')
                    ->where('meeting_participants.meeting_id', $meetingId)
                    ->where('meeting_participants.status', 'Admitted')
                    ->findAll();
    }

    public function getWaiting(int $meetingId): array
    {
        return $this->where('meeting_id', $meetingId)
                    ->where('status', 'Waiting')
                    ->findAll();
    }

    public function findByMeetingAndUser(int $meetingId, int $userId): ?array
    {
        return $this->where('meeting_id', $meetingId)
                    ->where('user_id', $userId)
                    ->first();
    }
}
