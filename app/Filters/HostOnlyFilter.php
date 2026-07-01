<?php

namespace App\Filters;

use App\Models\MeetingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class HostOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        $user = session()->get('auth_user');
        if (!$user) {
            return response()->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        // Extract meeting UUID from URI
        $segments = $request->getUri()->getSegments();
        $uuid     = null;
        foreach ($segments as $i => $seg) {
            if ($seg === 'meetings' && isset($segments[$i + 1])) {
                $uuid = $segments[$i + 1];
                break;
            }
        }

        if (!$uuid) {
            return response()->setJSON(['error' => 'Meeting not found'])->setStatusCode(404);
        }

        $meetingModel = new MeetingModel();
        $meeting      = $meetingModel->findByUuid($uuid);

        if (!$meeting) {
            return response()->setJSON(['error' => 'Meeting not found'])->setStatusCode(404);
        }

        if ((int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return response()->setJSON(['error' => 'Forbidden', 'message' => 'Host access required'])->setStatusCode(403);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
