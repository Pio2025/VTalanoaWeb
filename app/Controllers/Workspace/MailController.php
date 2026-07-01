<?php
namespace App\Controllers\Workspace;

use App\Controllers\BaseController;
use App\Models\WsMailModel;
use App\Models\UserModel;

class MailController extends BaseController
{
    private WsMailModel $mailModel;
    public function __construct() { $this->mailModel = new WsMailModel(); }

    /** GET /api/workspace/mail?folder=inbox|sent */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user   = session('auth_user');
        $folder = $this->request->getGet('folder') ?? 'inbox';
        $mails  = $folder === 'sent'
            ? $this->mailModel->getSent($user['user_id'])
            : $this->mailModel->getInbox($user['user_id']);
        return $this->response->setJSON($mails);
    }

    /** GET /api/workspace/mail/{id} */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $mail = $this->mailModel->getWithRecipients($id);
        if (!$mail) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);

        // Mark read for this recipient
        $db = \Config\Database::connect();
        $db->table('workspace_mail_recipients')
            ->where('mail_id', $id)->where('user_id', $user['user_id'])
            ->update(['is_read' => 1]);

        return $this->response->setJSON($mail);
    }

    /** POST /api/workspace/mail */
    public function send(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $data = $this->request->getJSON(true) ?? [];

        $subject = trim($data['subject'] ?? '');
        $body    = trim($data['body']    ?? '');
        $toIds   = array_filter(array_map('intval', $data['to'] ?? []));

        if (!$subject) $subject = '(no subject)';
        if (!$body)    return $this->response->setStatusCode(400)->setJSON(['error' => 'Body required']);
        if (!$toIds)   return $this->response->setStatusCode(400)->setJSON(['error' => 'At least one recipient required']);

        $threadId = bin2hex(random_bytes(16));
        $mailId   = $this->mailModel->insert([
            'thread_id'    => $threadId,
            'from_user_id' => $user['user_id'],
            'subject'      => $subject,
            'body'         => $body,
            'is_draft'     => 0,
        ], true);

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        foreach ($toIds as $uid) {
            $db->table('workspace_mail_recipients')->insert([
                'mail_id' => $mailId, 'user_id' => $uid, 'type' => 'to',
                'folder'  => 'inbox', 'is_read' => 0,
            ]);
        }
        foreach (array_filter(array_map('intval', $data['cc'] ?? [])) as $uid) {
            $db->table('workspace_mail_recipients')->insert([
                'mail_id' => $mailId, 'user_id' => $uid, 'type' => 'cc',
                'folder'  => 'inbox', 'is_read' => 0,
            ]);
        }

        return $this->response->setJSON(['ok' => true, 'mail_id' => $mailId]);
    }

    /** DELETE /api/workspace/mail/{id} — move to trash for recipient */
    public function trash(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $db   = \Config\Database::connect();
        $db->table('workspace_mail_recipients')
            ->where('mail_id', $id)->where('user_id', $user['user_id'])
            ->update(['folder' => 'trash']);
        return $this->response->setJSON(['ok' => true]);
    }

    /** PATCH /api/workspace/mail/{id}/star */
    public function star(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user    = session('auth_user');
        $db      = \Config\Database::connect();
        $current = $db->select('is_starred')->from('workspace_mail_recipients')
            ->where('mail_id', $id)->where('user_id', $user['user_id'])
            ->get()->getRowArray();
        $new = $current ? ($current['is_starred'] ? 0 : 1) : 1;
        $db->table('workspace_mail_recipients')
            ->where('mail_id', $id)->where('user_id', $user['user_id'])
            ->update(['is_starred' => $new]);
        return $this->response->setJSON(['starred' => (bool)$new]);
    }
}
