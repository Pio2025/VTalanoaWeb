<?php
namespace App\Controllers\Workspace;

use App\Controllers\BaseController;
use App\Models\WsDocModel;

class DocController extends BaseController
{
    private WsDocModel $docs;
    public function __construct() { $this->docs = new WsDocModel(); }

    /** GET /api/workspace/docs */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        return $this->response->setJSON($this->docs->getForUser($user['user_id']));
    }

    /** POST /api/workspace/docs */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $data = $this->request->getJSON(true) ?? [];
        $id   = $this->docs->insert([
            'title'    => trim($data['title'] ?? 'Untitled Document'),
            'content'  => $data['content'] ?? '',
            'owner_id' => $user['user_id'],
            'is_public'=> 1,
        ], true);
        return $this->response->setJSON($this->docs->find($id));
    }

    /** GET /api/workspace/docs/{id} */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $doc = $this->docs->find($id);
        if (!$doc) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($doc);
    }

    /** PUT /api/workspace/docs/{id} */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $doc  = $this->docs->find($id);
        if (!$doc) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        if ($doc['owner_id'] !== $user['user_id'] && !$doc['is_public']) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }
        $data = $this->request->getJSON(true) ?? [];
        $upd  = [];
        if (isset($data['title']))   $upd['title']   = trim($data['title']) ?: 'Untitled Document';
        if (isset($data['content'])) $upd['content'] = $data['content'];
        if ($upd) $this->docs->update($id, $upd);
        return $this->response->setJSON(['ok' => true, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /** DELETE /api/workspace/docs/{id} */
    public function destroy(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $doc  = $this->docs->find($id);
        if (!$doc || $doc['owner_id'] !== $user['user_id']) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }
        $this->docs->delete($id);
        return $this->response->setJSON(['ok' => true]);
    }
}
