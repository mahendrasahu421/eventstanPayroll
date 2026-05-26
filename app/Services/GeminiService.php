<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) env('GEMINI_API_KEY', '');
        $this->model = (string) env('GEMINI_MODEL', 'gemini-1.5-flash');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->model);
    }

    /**
     * Ask Gemini with a text prompt.
     */
    public function generateText(string $prompt, array $options = []): string
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Gemini is not configured. Please set GEMINI_API_KEY and GEMINI_MODEL in .env');
        }

        $timeoutSeconds = (int) Arr::get($options, 'timeout', 30);
        $temperature = (float) Arr::get($options, 'temperature', 0.2);
        $maxOutputTokens = (int) Arr::get($options, 'max_output_tokens', 512);

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            urlencode($this->model),
            urlencode($this->apiKey)
        );

        // Basic safety/prompting: instruct the model to be precise and not invent payroll data.
        $finalPrompt = trim($prompt);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $finalPrompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxOutputTokens,
            ],
        ];

        $client = $this->httpClient($timeoutSeconds);

        /** @var Response $resp */
        $resp = $client->post($endpoint, $payload);

        if (! $resp->successful()) {
            Log::error('Gemini API error', [
                'status' => $resp->status(),
                'body' => $this->safeSnippet($resp->body()),
            ]);
            throw new \RuntimeException('Gemini request failed.');
        }

        $json = $resp->json();

        // Expected response shape (varies by model):
        // candidates[0].content.parts[0].text
        $text = data_get($json, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            // Fallback: sometimes 'parts' might differ
            $fallback = data_get($json, 'candidates.0.content.parts');
            Log::warning('Gemini response missing text part', ['parts' => $fallback]);
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        return trim((string) $text);
    }

    private function httpClient(int $timeoutSeconds): PendingRequest
    {
        return Http::timeout($timeoutSeconds)
            ->connectTimeout(min(10, $timeoutSeconds))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
    }

    private function safeSnippet(mixed $body, int $limit = 1500): string
    {
        $str = is_string($body) ? $body : json_encode($body);
        return mb_substr((string) $str, 0, $limit);
    }
}

