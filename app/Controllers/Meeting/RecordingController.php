<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\RecordingModel;

class RecordingController extends BaseController
{
    private MeetingModel $meetingModel;
    private RecordingModel $recordingModel;

    public function __construct()
    {
        $this->meetingModel   = new MeetingModel();
        $this->recordingModel = new RecordingModel();
    }

    public function apiList(string $uuid): mixed
    {
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }
        return $this->response->setJSON(['data' => $this->recordingModel->getByMeeting($meeting['meeting_id'])]);
    }

    public function apiCreate(string $uuid): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);

        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $fileName = 'vtalanoa-' . $meeting['meeting_uuid'] . '-' . time() . '.webm';
        $id       = $this->recordingModel->insert([
            'meeting_id' => $meeting['meeting_id'],
            'user_id'    => $user['user_id'],
            'file_name'  => $fileName,
            'started_at' => date('Y-m-d H:i:s'),
            'status'     => 'Recording',
        ]);

        return $this->response->setJSON(['recording_id' => $id, 'file_name' => $fileName])->setStatusCode(201);
    }

    public function apiStop(string $uuid, int $recordingId): mixed
    {
        $recording = $this->recordingModel->find($recordingId);
        if (!$recording) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $data     = $this->request->getJSON(true);
        $duration = $data['duration_seconds'] ?? null;
        $start    = strtotime($recording['started_at']);
        $calcDur  = $duration ?? (time() - $start);

        $this->recordingModel->update($recordingId, [
            'status'           => 'Completed',
            'ended_at'         => date('Y-m-d H:i:s'),
            'duration_seconds' => $calcDur,
        ]);

        return $this->response->setJSON(['message' => 'Recording logged.']);
    }

    public function apiStopLatest(string $uuid): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($uuid);
        if (!$meeting) {
            return $this->response->setJSON(['error' => 'Not found'])->setStatusCode(404);
        }

        $db      = \Config\Database::connect();
        $recording = $db->table('recordings')
            ->where('meeting_id', $meeting['meeting_id'])
            ->where('user_id', $user['user_id'])
            ->where('status', 'Recording')
            ->orderBy('started_at', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$recording) {
            return $this->response->setJSON(['message' => 'No active recording found.']);
        }

        $data    = $this->request->getJSON(true);
        $duration = $data['duration_seconds'] ?? (time() - strtotime($recording['started_at']));

        $this->recordingModel->update($recording['recording_id'], [
            'status'           => 'Completed',
            'ended_at'         => date('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
        ]);

        return $this->response->setJSON(['message' => 'Recording logged.']);
    }
}
