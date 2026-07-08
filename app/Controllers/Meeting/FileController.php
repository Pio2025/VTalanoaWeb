<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\MeetingMessageAttachmentModel;

class FileController extends BaseController
{
    private MeetingModel $meetingModel;
    private MeetingMessageAttachmentModel $attachmentModel;

    public function __construct()
    {
        $this->meetingModel    = new MeetingModel();
        $this->attachmentModel = new MeetingMessageAttachmentModel();
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
