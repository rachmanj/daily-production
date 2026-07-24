<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiInsightService
{
    protected string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openrouter.api_key');
        $this->model = (string) config('services.openrouter.model', 'openai/gpt-4o-mini');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function narrativeSummary(array $data): ?string
    {
        return $this->chat("Buat ringkasan naratif singkat (Bahasa Indonesia) untuk data produksi harian berikut:\n".json_encode($data, JSON_PRETTY_PRINT));
    }

    public function explainAnomaly(string $context): ?string
    {
        return $this->chat("Jelaskan kemungkinan penyebab anomali fuel consumption berikut (Bahasa Indonesia, singkat):\n{$context}");
    }

    public function nlQuery(string $question, array $context): ?string
    {
        return $this->chat("Konteks data:\n".json_encode($context)."\n\nPertanyaan: {$question}\nJawab singkat dalam Bahasa Indonesia.");
    }

    protected function chat(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }
        } catch (\Exception) {
            return null;
        }

        return null;
    }
}
