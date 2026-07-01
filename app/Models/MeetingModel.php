<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingModel extends Model
{
    protected $table      = 'meetings';
    protected $primaryKey = 'meeting_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'meeting_uuid', 'meeting_token', 'host_user_id', 'title', 'description', 'password',
        'scheduled_start', 'scheduled_end', 'actual_start', 'actual_end',
        'status', 'waiting_room', 'allow_recording', 'max_participants',
    ];

    protected $useTimestamps = true;

    public function findByUuid(string $uuid): ?array
    {
        return $this->where('meeting_uuid', $uuid)->first();
    }

    public function findByToken(string $token): ?array
    {
        return $this->where('meeting_token', $token)->first();
    }

    public function getUserMeetings(int $userId, int $page = 1, int $perPage = 10): array
    {
        return $this->where('host_user_id', $userId)
                    ->orderBy('scheduled_start', 'DESC')
                    ->paginate($perPage, 'default', $page);
    }

    public function getUpcoming(int $userId, int $limit = 5): array
    {
        return $this->where('host_user_id', $userId)
                    ->where('status', 'Scheduled')
                    ->where('scheduled_start >=', date('Y-m-d H:i:s'))
                    ->orderBy('scheduled_start', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getWithHost(string $token): ?array
    {
        return $this->select('meetings.*, users.fname, users.lname, users.profile_photo, users.username')
                    ->join('users', 'users.user_id = meetings.host_user_id', 'left')
                    ->where('meetings.meeting_token', $token)
                    ->first();
    }
}
