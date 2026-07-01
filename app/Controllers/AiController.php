<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Proxies AI requests to Google AI Studio (Gemini).
 * Keeps the API key server-side; client authenticates via JWT filter.
 */
class AiController extends BaseController
{
    private const GEMINI_MODEL   = 'gemini-2.5-flash';
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const MAX_TOKENS     = 1500;

    /** POST /api/ai/chat */
    public function chat(): mixed
    {
        $apiKey = env('GOOGLE_AI_API_KEY');
        if (!$apiKey) {
            return $this->response->setStatusCode(503)
                ->setJSON(['error' => 'AI service not configured on this server.']);
        }

        $data       = $this->request->getJSON(true) ?? [];
        $type       = $data['type']       ?? 'qa';
        $transcript = trim($data['transcript'] ?? '');
        $question   = trim($data['question']   ?? '');

        if (!$transcript && !$question) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'No transcript or question provided.']);
        }

        if ($type === 'qa' && !$question) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Please enter a question.']);
        }

        $prompt = $this->buildPrompt($type, $transcript, $question);

        try {
            $result = $this->callGemini($apiKey, $prompt);
            return $this->response->setJSON(['response' => $result]);
        } catch (\Exception $e) {
            log_message('error', '[AiController] ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                ->setJSON(['error' => 'AI service error — please try again.']);
        }
    }

    private function buildPrompt(string $type, string $transcript, string $question): string
    {
        $ctx = $transcript
            ? "Meeting transcript:\n\"\"\"\n{$transcript}\n\"\"\"\n\n"
            : '';

        return match ($type) {
            'summary' => $ctx .
                'Please write a concise meeting summary. Structure it with these sections: ' .
                '**Key Topics** (bullet list), **Decisions Made** (bullet list), **Next Steps** (bullet list). ' .
                'Keep it professional and under 300 words.',

            'actions' => $ctx .
                'List every action item from this meeting transcript. ' .
                'For each item provide: the task, who is responsible (if mentioned), and the deadline (if mentioned). ' .
                'Format as a numbered list. If no owner or deadline is stated, omit those fields.',

            'email' => $ctx .
                'Draft a professional meeting follow-up email. ' .
                'Include: a one-sentence intro, a brief summary section, key decisions, action items with owners, ' .
                'and a polite closing. Keep it under 250 words. Use plain professional language.',

            'qa' => $ctx .
                "Based on the meeting transcript above, answer this question concisely:\n{$question}",

            default => $ctx . $question,
        };
    }

    private function callGemini(string $apiKey, string $prompt): string
    {
        $url = self::GEMINI_API_URL . self::GEMINI_MODEL . ':generateContent?key=' . $apiKey;

        $payload = json_encode([
            'contents'         => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => self::MAX_TOKENS,
                'temperature'     => 0.7,
            ],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'content-type: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new \RuntimeException("cURL error: {$curlErr}");
        }
        if ($httpCode !== 200) {
            throw new \RuntimeException("Gemini API HTTP {$httpCode}: " . substr($raw, 0, 300));
        }

        $body = json_decode($raw, true);

        // Gemini can stop early (e.g. MAX_TOKENS, SAFETY) — surface that instead of a silent empty reply
        $finishReason = $body['candidates'][0]['finishReason'] ?? null;
        $text          = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null) {
            if ($finishReason === 'SAFETY') {
                throw new \RuntimeException('Gemini blocked the response for safety reasons');
            }
            throw new \RuntimeException('Empty AI response body: ' . substr($raw, 0, 300));
        }

        return $text;
    }
}
