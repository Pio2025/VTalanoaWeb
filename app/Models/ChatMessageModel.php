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
        $builder = $this->select('chat_messages.*, chat_attachments.file_url, chat_attachments.file_name, chat_attachments.mime_type, chat_attachments.file_size')
                        ->join('chat_attachments', 'chat_attachments.message_id = chat_messages.message_id', 'left')
                        ->where('chat_messages.meeting_id', $meetingId);
        if (!$includePrivate) {
            $builder->where('chat_messages.is_private', 0);
        }
        return $builder->orderBy('chat_messages.sent_at', 'ASC')->findAll();
    }
}
