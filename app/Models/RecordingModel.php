<?php

namespace App\Models;

use CodeIgniter\Model;

class RecordingModel extends Model
{
    protected $table      = 'recordings';
    protected $primaryKey = 'recording_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'meeting_id', 'user_id', 'file_name', 'duration_seconds',
        'started_at', 'ended_at', 'status',
    ];

    protected $useTimestamps = true;

    public function getByMeeting(int $meetingId): array
    {
        return $this->select('recordings.*, users.fname, users.lname')
                    ->join('users', 'users.user_id = recordings.user_id')
                    ->where('recordings.meeting_id', $meetingId)
                    ->orderBy('recordings.started_at', 'DESC')
                    ->findAll();
    }
}
