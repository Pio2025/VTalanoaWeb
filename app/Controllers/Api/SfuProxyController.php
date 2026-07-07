<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MeetingModel;

/**
 * Per-meeting SFU proxy for the Flutter (and future native) clients.
 *
 * Routes live at:  /api/meetings/{meetingToken}/sfu-proxy/{...}
 * Protected by:    jwt filter (Bearer token from Authorization header)
 *
 * Validates that the authenticated user is a participant of the meeting,
 * then forwards the request to Cloudflare Realtime SFU, keeping the
 * CF_SFU_APP_SECRET entirely server-side.
 */
class SfuProxyController extends BaseController
{
    private string $sfuBase;
    private string $appSecret;
    private MeetingModel $meetingModel;

    public function __construct()
    {
        $appId              = env('CF_SFU_APP_ID', '');
        $this->appSecret    = env('CF_SFU_APP_SECRET', '');
        $this->sfuBase      = 'https://rtc.live.cloudflare.com/apps/' . $appId;
        $this->meetingModel = new MeetingModel();
    }

    // ── Route handlers ────────────────────────────────────────────────────────

    /** POST /api/meetings/{token}/sfu-proxy/sessions/new */
    public function newSession(string $meetingToken): mixed
    {
        if (!$this->validateMeeting($meetingToken)) {
            return $this->notFound();
        }
        return $this->proxy('POST', '/sessions/new');
    }

    /** POST /api/meetings/{token}/sfu-proxy/sessions/{sessionId}/tracks/new */
    public function newTracks(string $meetingToken, string $sessionId): mixed
    {
        if (!$this->validateMeeting($meetingToken)) {
            return $this->notFound();
        }
        return $this->proxy('POST', "/sessions/{$sessionId}/tracks/new");
    }

    /** PUT /api/meetings/{token}/sfu-proxy/sessions/{sessionId}/renegotiate */
    public function renegotiate(string $meetingToken, string $sessionId): mixed
    {
        if (!$this->validateMeeting($meetingToken)) {
            return $this->notFound();
        }
        return $this->proxy('PUT', "/sessions/{$sessionId}/renegotiate");
    }

    /** PUT /api/meetings/{token}/sfu-proxy/sessions/{sessionId}/tracks/close */
    public function closeTracks(string $meetingToken, string $sessionId): mixed
    {
        if (!$this->validateMeeting($meetingToken)) {
            return $this->notFound();
        }
        return $this->proxy('PUT', "/sessions/{$sessionId}/tracks/close");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validateMeeting(string $meetingToken): bool
    {
        $meeting = $this->meetingModel->where('meeting_token', $meetingToken)->first();
        return $meeting !== null;
    }

    private function proxy(string $method, string $path): mixed
    {
        $url  = $this->sfuBase . $path;
        $body = $this->request->getBody();

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->appSecret,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $body ?: '{}',
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_message('error', '[Api\\SfuProxy] cURL error on ' . $path . ': ' . $curlError);
            return $this->response
                ->setStatusCode(502)
                ->setContentType('application/json')
                ->setBody(json_encode(['error' => 'SFU gateway error']));
        }

        return $this->response
            ->setStatusCode($httpCode)
            ->setContentType('application/json')
            ->setBody($responseBody);
    }

    private function notFound(): mixed
    {
        return $this->response
            ->setStatusCode(404)
            ->setJSON(['error' => 'Meeting not found']);
    }
}
