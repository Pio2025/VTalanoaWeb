<?php
namespace App\Controllers\Workspace;

use App\Controllers\BaseController;
use App\Models\WsChannelModel;
use App\Models\WsMessageModel;

class ChatController extends BaseController
{
    private WsChannelModel  $channels;
    private WsMessageModel  $messages;

    public function __construct()
    {
        $this->channels = new WsChannelModel();
        $this->messages = new WsMessageModel();
    }

    /** GET /api/workspace/channels */
    public function channels(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $this->channels->ensureGeneral($user['user_id']);
        return $this->response->setJSON($this->channels->getForUser($user['user_id']));
    }

    /** POST /api/workspace/channels */
    public function createChannel(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $data = $this->request->getJSON(true) ?? [];
        $name = strtolower(preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($data['name'] ?? ''))));
        if (!$name) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Channel name required']);
        }
        if ($this->channels->where('name', $name)->first()) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Channel already exists']);
        }
        $id = $this->channels->insert([
            'name'        => $name,
            'description' => trim($data['description'] ?? ''),
            'type'        => in_array($data['type'] ?? '', ['public','private']) ? $data['type'] : 'public',
            'created_by'  => $user['user_id'],
        ], true);
        \Config\Database::connect()->table('workspace_channel_members')->insert([
            'channel_id' => $id, 'user_id' => $user['user_id'], 'joined_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON($this->channels->find($id));
    }

    /** GET /api/workspace/channels/{id}/messages */
    public function messages(int $channelId): \CodeIgniter\HTTP\ResponseInterface
    {
        $before = (int)($this->request->getGet('before') ?? 0);
        return $this->response->setJSON($this->messages->getForChannel($channelId, 60, $before));
    }

    /** POST /api/workspace/channels/{id}/messages */
    public function send(int $channelId): \CodeIgniter\HTTP\ResponseInterface
    {
        $user    = session('auth_user');
        $data    = $this->request->getJSON(true) ?? [];
        $content = trim($data['content'] ?? '');
        if (!$content && empty($data['file_url'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Empty message']);
        }
        $id  = $this->messages->insert([
            'channel_id' => $channelId,
            'user_id'    => $user['user_id'],
            'content'    => $content,
            'type'       => empty($data['file_url']) ? 'text' : 'file',
            'file_url'   => $data['file_url'] ?? null,
            'file_name'  => $data['file_name'] ?? null,
        ], true);
        $msg = $this->messages
            ->select('workspace_messages.*, users.fname, users.lname, users.profile_photo')
            ->join('users', 'users.user_id = workspace_messages.user_id', 'left')
            ->find($id);
        // Mark channel read for sender
        $this->channels->markRead($channelId, $user['user_id']);
        return $this->response->setJSON($msg);
    }

    /** GET /api/workspace/users */
    public function users(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db    = \Config\Database::connect();
        $users = $db->select('user_id, fname, lname, email, username, profile_photo')
                    ->from('users')->orderBy('fname')->get()->getResultArray();
        return $this->response->setJSON($users);
    }
}
