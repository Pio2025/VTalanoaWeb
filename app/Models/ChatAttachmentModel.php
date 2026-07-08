<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatAttachmentModel extends Model
{
    protected $table      = 'chat_attachments';
    protected $primaryKey = 'attachment_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'message_id', 'meeting_id', 'file_url', 'file_name', 'mime_type', 'file_size',
    ];

    protected $useTimestamps = true;

    public function getByMeeting(int $meetingId): array
    {
        return $this->where('meeting_id', $meetingId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
