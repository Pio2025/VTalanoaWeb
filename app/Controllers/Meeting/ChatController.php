<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\ChatMessageModel;
use App\Models\ChatAttachmentModel;

class ChatController extends BaseController
{
    private MeetingModel $meetingModel;
    private ChatMessageModel $chatModel;
    private ChatAttachmentModel $attachmentModel;

    public function __construct()
    {
        $this->meetingModel    = new MeetingModel();
        $this->chatModel       = new ChatMessageModel();
        $this->attachmentModel = new ChatAttachmentModel();
    }

    public function apiList(string $uuid): mixed
    {
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }
        return $this->response->setJSON(['data' => $this->chatModel->getByMeeting($meeting['meeting_id'])]);
    }

    public function apiStore(string $uuid): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $data          = $this->request->getJSON(true);
        $message       = trim(strip_tags($data['message'] ?? ''));
        $attachmentUrl = $data['attachment_url'] ?? null;

        if (empty($message) && empty($attachmentUrl)) {
            return $this->response->setJSON(['error' => 'Message cannot be empty'])->setStatusCode(422);
        }

        $id = $this->chatModel->insert([
            'meeting_id'   => $meeting['meeting_id'],
            'sender_id'    => $user['user_id'],
            'sender_name'  => $user['fname'] . ' ' . $user['lname'],
            'message'      => $message,
            'is_private'   => $data['is_private'] ?? 0,
            'recipient_id' => $data['recipient_id'] ?? null,
            'sent_at'      => date('Y-m-d H:i:s'),
        ]);

        if (!empty($attachmentUrl)) {
            $this->attachmentModel->insert([
                'message_id' => $id,
                'meeting_id' => $meeting['meeting_id'],
                'file_url'   => $attachmentUrl,
                'file_name'  => $data['attachment_name'] ?? basename((string) $attachmentUrl),
                'mime_type'  => $data['attachment_mime'] ?? null,
                'file_size'  => $data['attachment_size'] ?? null,
            ]);
        }

        return $this->response->setJSON(['message_id' => $id])->setStatusCode(201);
    }

    public function apiUpload(): mixed
    {
        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['error' => 'No valid file uploaded'])->setStatusCode(422);
        }

        // Extension → canonical MIME map. Validated against client MIME type.
        // Avoids finfo_file() which fails on Bluehost shared hosting (/tmp restricted).
        $allowedExtMime = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $ext        = strtolower($file->getClientExtension());
        $clientMime = $file->getClientMimeType();
        $clientName = $file->getClientName();
        $fileSize   = $file->getSize();

        if (!isset($allowedExtMime[$ext])) {
            return $this->response->setJSON(['error' => 'File type not allowed. Accepted: images, PDF, Word, PowerPoint, Excel.'])->setStatusCode(422);
        }

        // Ensure the browser-reported MIME also matches the extension's expected type.
        if ($clientMime !== $allowedExtMime[$ext]) {
            return $this->response->setJSON(['error' => 'File type mismatch. Accepted: images, PDF, Word, PowerPoint, Excel.'])->setStatusCode(422);
        }

        if ($fileSize > 10 * 1024 * 1024) {
            return $this->response->setJSON(['error' => 'File too large. Maximum 10 MB.'])->setStatusCode(422);
        }

        $dest = FCPATH . 'uploads/chat/';
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($dest, $newName);

        return $this->response->setJSON([
            'url'  => base_url('uploads/chat/' . $newName),
            'name' => $clientName,
            'type' => $allowedExtMime[$ext],
            'size' => $fileSize,
        ])->setStatusCode(201);
    }
}
