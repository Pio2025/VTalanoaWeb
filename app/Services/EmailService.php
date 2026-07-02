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

        $driver = strtolower(env('MAIL_DRIVER', 'smtp'));

        if ($driver === 'mail') {
            // Use PHP's built-in mail() — no SMTP auth needed, works on shared hosting
            $this->mailer->isMail();
        } else {
            // SMTP driver
            $this->mailer->isSMTP();
            $this->mailer->Host     = env('MAIL_HOST', 'localhost');
            $this->mailer->Port     = (int) env('MAIL_PORT', 587);
            $this->mailer->SMTPAuth = true;
            $this->mailer->AuthType = 'LOGIN'; // cPanel mail servers require LOGIN auth
            $this->mailer->Username = env('MAIL_USER', '');
            $this->mailer->Password = env('MAIL_PASS', '');

            if ((int) env('MAIL_PORT', 587) === 465) {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Enable SMTP debug — set MAIL_DEBUG=true in .env to capture full SMTP log
            if (env('MAIL_DEBUG', false)) {
                $this->mailer->SMTPDebug  = 2;
                $this->mailer->Debugoutput = function (string $str, int $level): void {
                    log_message('debug', '[PHPMailer] ' . trim($str));
                };
            }
        }

        $this->mailer->setFrom(
            env('MAIL_FROM_EMAIL', 'noreply@navulifiji.com'),
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
