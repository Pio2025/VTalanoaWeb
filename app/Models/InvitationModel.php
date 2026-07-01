<?php

namespace App\Models;

use CodeIgniter\Model;

class InvitationModel extends Model
{
    protected $table      = 'meeting_invitations';
    protected $primaryKey = 'invite_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'meeting_id', 'invited_by', 'invitee_email', 'invitee_user_id',
        'token', 'status', 'sent_at', 'responded_at',
    ];

    protected $useTimestamps = true;

    public function findByToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    public function getByMeeting(int $meetingId): array
    {
        return $this->where('meeting_id', $meetingId)->findAll();
    }
}
