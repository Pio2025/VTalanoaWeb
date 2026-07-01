<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMessageModel extends Model
{
    protected $table      = 'chat_messages';
    protected $primaryKey = 'message_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'meeting_id', 'sender_id', 'sender_name', 'message',
        'is_private', 'recipient_id', 'sent_at',
    ];

    protected $useTimestamps = true;

    public function getByMeeting(int $meetingId, bool $includePrivate = false): array
    {
        $builder = $this->where('meeting_id', $meetingId);
        if (!$includePrivate) {
            $builder->where('is_private', 0);
        }
        return $builder->orderBy('sent_at', 'ASC')->findAll();
    }
}
