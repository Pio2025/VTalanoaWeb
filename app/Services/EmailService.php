<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host     = env('MAIL_HOST', 'smtp.gmail.com');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = env('MAIL_USER', '');
        $this->mailer->Password = env('MAIL_PASS', '');
        $this->mailer->Port     = (int) env('MAIL_PORT', 587);

        $port = (int) env('MAIL_PORT', 587);
        if ($port === 465) {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        // Bypass SSL cert verification — safe for local/dev (WAMP) and self-signed certs
        $this->mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $this->mailer->setFrom(
            env('MAIL_FROM_EMAIL', 'noreply@navulimeet.com'),
            env('MAIL_FROM_NAME',  'VTalanoa')
        );
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function sendMeetingCreated(string $toEmail, string $toName, array $meeting, string $joinLink, string $plainPassword = ''): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = 'Meeting Created: ' . $meeting['title'];
            $this->mailer->Body    = view('email/meeting_invite', [
                'hostName'      => $toName,
                'meeting'       => $meeting,
                'joinLink'      => $joinLink,
                'plainPassword' => $plainPassword,
                'isHostCopy'    => true,
            ]);
            $this->mailer->AltBody = "Your meeting has been created. Join link: {$joinLink}";
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            log_message('error', '[EmailService] sendMeetingCreated failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendMeetingInvitation(string $toEmail, string $toName, array $meeting, string $joinLink, string $hostName = 'The Host'): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = "You're invited to: {$meeting['title']}";
            $this->mailer->Body    = view('email/meeting_invite', [
                'hostName'      => $hostName,
                'meeting'       => $meeting,
                'joinLink'      => $joinLink,
                'plainPassword' => $meeting['password'] ?? '',
                'isHostCopy'    => false,
            ]);
            $this->mailer->AltBody = "You have been invited to a meeting. Join here: {$joinLink}";
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            log_message('error', '[EmailService] sendMeetingInvitation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = 'Reset Your VTalanoa Password';
            $this->mailer->Body    = view('email/password_reset', [
                'name'      => $toName,
                'resetLink' => $resetLink,
            ]);
            $this->mailer->AltBody = "Reset your VTalanoa password by visiting this link: {$resetLink}\n\nThis link expires in 1 hour.";
            $this->mailer->send();
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[EmailService] sendPasswordReset failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendEmailVerification(string $toEmail, string $toName, string $verifyLink): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = 'Verify Your VTalanoa Account';
            $this->mailer->Body    = view('email/verify_email', [
                'name'       => $toName,
                'verifyLink' => $verifyLink,
            ]);
            $this->mailer->AltBody = "Verify your email: {$verifyLink}";
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            log_message('error', '[EmailService] sendEmailVerification failed: ' . $e->getMessage());
            return false;
        }
    }
}
