<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Proxies Cloudflare Realtime SFU session/track API calls from the browser.
 * Keeps CF_SFU_APP_SECRET server-side; only the browser JWT is validated.
 *
 * Base URL forwarded to: https://rtc.live.cloudflare.com/apps/{appId}
 */
class SfuProxyController extends BaseController
{
    private string $sfuBase;
    private string $appSecret;

    public function __construct()
    {
        $appId           = env('CF_SFU_APP_ID');
        $this->appSecret = env('CF_SFU_APP_SECRET');
        $this->sfuBase   = 'https://rtc.live.cloudflare.com/apps/' . $appId;
    }

    /** POST /sfu-proxy/sessions/new */
    public function newSession(): mixed
    {
        return $this->proxy('POST', '/sessions/new');
    }

    /** POST /sfu-proxy/sessions/{sessionId}/tracks/new */
    public function newTracks(string $sessionId): mixed
    {
        return $this->proxy('POST', "/sessions/{$sessionId}/tracks/new");
    }

    /** PUT /sfu-proxy/sessions/{sessionId}/renegotiate */
    public function renegotiate(string $sessionId): mixed
    {
        return $this->proxy('PUT', "/sessions/{$sessionId}/renegotiate");
    }

    /** PUT /sfu-proxy/sessions/{sessionId}/tracks/close */
    public function closeTracks(string $sessionId): mixed
    {
        return $this->proxy('PUT', "/sessions/{$sessionId}/tracks/close");
    }

    // ---------------------------------------------------------------

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
            log_message('error', '[SfuProxy] cURL error: ' . $curlError);
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
}
