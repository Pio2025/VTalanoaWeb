<?php
namespace App\Models;
use CodeIgniter\Model;

class WsDocModel extends Model
{
    protected $table      = 'workspace_docs';
    protected $primaryKey = 'doc_id';
    protected $returnType = 'array';
    protected $allowedFields = ['title', 'content', 'owner_id', 'is_public'];
    protected $useTimestamps = true;

    public function getForUser(int $userId): array
    {
        return $this->select('workspace_docs.doc_id, workspace_docs.title, workspace_docs.owner_id,
                workspace_docs.is_public, workspace_docs.created_at, workspace_docs.updated_at,
                users.fname, users.lname')
            ->join('users', 'users.user_id = workspace_docs.owner_id', 'left')
            ->groupStart()
                ->where('workspace_docs.owner_id', $userId)
                ->orWhere('workspace_docs.is_public', 1)
            ->groupEnd()
            ->orderBy('workspace_docs.updated_at', 'DESC')
            ->findAll();
    }
}
