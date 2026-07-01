<?php
namespace App\Models;
use CodeIgniter\Model;

class WsMailModel extends Model
{
    protected $table      = 'workspace_mail';
    protected $primaryKey = 'mail_id';
    protected $returnType = 'array';
    protected $allowedFields = ['thread_id', 'from_user_id', 'subject', 'body', 'is_draft'];
    protected $useTimestamps = false;

    protected $beforeInsert = ['addTimestamp'];
    protected function addTimestamp(array $data): array
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    public function getInbox(int $userId): array
    {
        $db = \Config\Database::connect();
        return $db->select('workspace_mail.*, r.is_read, r.is_starred, r.folder,
            u.fname AS sender_fname, u.lname AS sender_lname, u.profile_photo AS sender_photo,
            u.email AS sender_email')
            ->from('workspace_mail')
            ->join('workspace_mail_recipients r',
                   'r.mail_id = workspace_mail.mail_id AND r.user_id = ' . $userId)
            ->join('users u', 'u.user_id = workspace_mail.from_user_id', 'left')
            ->where('r.folder', 'inbox')
            ->where('workspace_mail.is_draft', 0)
            ->orderBy('workspace_mail.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getSent(int $userId): array
    {
        $db = \Config\Database::connect();
        return $db->select('workspace_mail.*,
            GROUP_CONCAT(CONCAT(u2.fname, " ", u2.lname) SEPARATOR ", ") AS to_names')
            ->from('workspace_mail')
            ->join('workspace_mail_recipients r', 'r.mail_id = workspace_mail.mail_id AND r.type = "to"', 'left')
            ->join('users u2', 'u2.user_id = r.user_id', 'left')
            ->where('workspace_mail.from_user_id', $userId)
            ->where('workspace_mail.is_draft', 0)
            ->groupBy('workspace_mail.mail_id')
            ->orderBy('workspace_mail.created_at', 'DESC')
            ->get()->getResultArray();
    }

    public function getWithRecipients(int $mailId): ?array
    {
        $mail = $this->find($mailId);
        if (!$mail) return null;
        $db = \Config\Database::connect();
        $mail['recipients'] = $db->select('r.type, r.user_id, u.fname, u.lname, u.email, u.profile_photo')
            ->from('workspace_mail_recipients r')
            ->join('users u', 'u.user_id = r.user_id', 'left')
            ->where('r.mail_id', $mailId)
            ->get()->getResultArray();
        $sender = $db->select('fname, lname, email, profile_photo')
            ->from('users')
            ->where('user_id', $mail['from_user_id'])
            ->get()->getRowArray();
        $mail['sender'] = $sender;
        return $mail;
    }
}
