<?php

namespace App\Controllers\Meeting;

use App\Controllers\BaseController;
use App\Models\MeetingModel;
use App\Models\InvitationModel;
use App\Models\UserModel;
use App\Services\EmailService;

class InvitationController extends BaseController
{
    private MeetingModel $meetingModel;
    private InvitationModel $invitationModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->meetingModel    = new MeetingModel();
        $this->invitationModel = new InvitationModel();
        $this->userModel       = new UserModel();
    }

    public function apiSend(string $token): mixed
    {
        $user    = session()->get('auth_user');
        $meeting = $this->meetingModel->findByToken($token);

        if (!$meeting || (int)$meeting['host_user_id'] !== (int)$user['user_id']) {
            return $this->response->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
        }

        $data   = $this->request->getJSON(true);
        $emails = $data['emails'] ?? [];
        $sent   = [];

        foreach ($emails as $email) {
            $email      = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
            if (!$email) continue;

            $inviteToken = bin2hex(random_bytes(32));
            $invitee     = $this->userModel->findByEmail($email);
            $joinUrl     = base_url('join/' . $token) . '?invite=' . $inviteToken;

            $inviteId = $this->invitationModel->insert([
                'meeting_id'      => $meeting['meeting_id'],
                'invited_by'      => $user['user_id'],
                'invitee_email'   => $email,
                'invitee_user_id' => $invitee ? $invitee['user_id'] : null,
                'token'           => $inviteToken,
                'status'          => 'Pending',
                'sent_at'         => date('Y-m-d H:i:s'),
            ]);

            try {
                $hostName = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
                (new EmailService())->sendMeetingInvitation(
                    $email,
                    $invitee ? $invitee['fname'] : 'Guest',
                    $meeting,
                    $joinUrl,
                    $hostName
                );
            } catch (\Exception $e) {
                log_message('error', 'Invite email failed: ' . $e->getMessage());
            }

            $sent[] = $email;
        }

        return $this->response->setJSON(['sent_to' => $sent]);
    }
}
