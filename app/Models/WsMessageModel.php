<?php
namespace App\Models;
use CodeIgniter\Model;

class WsMessageModel extends Model
{
    protected $table      = 'workspace_messages';
    protected $primaryKey = 'message_id';
    protected $returnType = 'array';
    protected $allowedFields = ['channel_id', 'user_id', 'content', 'type', 'file_url', 'file_name', 'is_deleted'];
    protected $useTimestamps = true;

    public function getForChannel(int $channelId, int $limit = 50, int $before = 0): array
    {
        $q = $this->select('workspace_messages.*, users.fname, users.lname, users.profile_photo')
            ->join('users', 'users.user_id = workspace_messages.user_id', 'left')
            ->where('channel_id', $channelId)
            ->where('is_deleted', 0)
            ->orderBy('workspace_messages.created_at', 'DESC')
            ->limit($limit);
        if ($before > 0) {
            $q->where('workspace_messages.message_id <', $before);
        }
        return array_reverse($q->findAll());
    }
}
