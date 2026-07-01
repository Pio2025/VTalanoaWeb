<?php
namespace App\Models;
use CodeIgniter\Model;

class WsChannelModel extends Model
{
    protected $table      = 'workspace_channels';
    protected $primaryKey = 'channel_id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'description', 'type', 'created_by'];
    protected $useTimestamps = true;

    public function getForUser(int $userId): array
    {
        $db = \Config\Database::connect();
        return $db->select('workspace_channels.*, wcm.last_read_at,
            (SELECT COUNT(*) FROM workspace_messages wm WHERE wm.channel_id = workspace_channels.channel_id AND wm.is_deleted = 0 AND (wcm.last_read_at IS NULL OR wm.created_at > wcm.last_read_at)) AS unread_count')
            ->from('workspace_channels')
            ->join('workspace_channel_members wcm',
                   'wcm.channel_id = workspace_channels.channel_id AND wcm.user_id = ' . $userId,
                   'left')
            ->groupStart()
                ->where('workspace_channels.type', 'public')
                ->orWhere('wcm.user_id', $userId)
            ->groupEnd()
            ->orderBy('workspace_channels.name', 'ASC')
            ->get()->getResultArray();
    }

    public function ensureGeneral(int $userId): void
    {
        $existing = $this->where('name', 'general')->first();
        if (!$existing) {
            $id = $this->insert([
                'name'        => 'general',
                'description' => 'Company-wide announcements and discussions',
                'type'        => 'public',
                'created_by'  => $userId,
            ], true);
            \Config\Database::connect()->table('workspace_channel_members')->insert([
                'channel_id' => $id, 'user_id' => $userId, 'joined_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function markRead(int $channelId, int $userId): void
    {
        $db = \Config\Database::connect();
        $db->table('workspace_channel_members')
            ->where('channel_id', $channelId)->where('user_id', $userId)
            ->update(['last_read_at' => date('Y-m-d H:i:s')]);
    }
}
