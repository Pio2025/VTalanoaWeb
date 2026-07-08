<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\ChatAttachmentModel;

class FileController extends BaseController
{
    private MeetingModel $meetingModel;
    private ChatAttachmentModel $attachmentModel;

    public function __construct()
    {
        $this->meetingModel    = new MeetingModel();
        $this->attachmentModel = new ChatAttachmentModel();
    }

    public function apiFiles(string $uuid): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }
        if (!$user || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        return $this->response->setJSON(['data' => $this->attachmentModel->getByMeeting($meeting['meeting_id'])]);
    }
}
