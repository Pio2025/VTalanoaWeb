<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingMessageModel extends Model
{
    protected $table      = 'meeting_messages';
    protected $primaryKey = 'message_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'meeting_id', 'sender_id', 'message', 'is_private', 'sent_at',
    ];

    protected $useTimestamps = true;

    /**
     * sender_id references meeting_participants.participant_id (not users.user_id)
     * so both registered and guest senders resolve through the same column.
     * Display name is derived here rather than stored, since guests have no
     * users row and a registered user's name can change after the message was sent.
     */
    public function getByMeeting(int $meetingId, bool $includePrivate = false): array
    {
        $builder = $this->select("
                meeting_messages.*,
                meeting_participants.guest_name,
                users.fname, users.lname,
                meeting_message_attachments.file_url,
                meeting_message_attachments.file_name,
                meeting_message_attachments.mime_type,
                meeting_message_attachments.file_size
            ")
            ->join('meeting_participants', 'meeting_participants.participant_id = meeting_messages.sender_id', 'left')
            ->join('users', 'users.user_id = meeting_participants.user_id', 'left')
            ->join('meeting_message_attachments', 'meeting_message_attachments.message_id = meeting_messages.message_id', 'left')
            ->where('meeting_messages.meeting_id', $meetingId);

        if (!$includePrivate) {
            $builder->where('meeting_messages.is_private', 0);
        }

        return $builder->orderBy('meeting_messages.sent_at', 'ASC')->findAll();
    }
}
