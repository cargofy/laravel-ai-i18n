<?php

namespace Cargofy\LaravelAiI18n\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGptTranslationService extends AbstractTranslationService
{
    protected string $apiKey;
    protected string $model;
    protected float $temperature;

    public function __construct()
    {
        $this->apiKey = config('laravel-ai-i18n.services.chatgpt.api_key');
        $this->model = config('laravel-ai-i18n.services.chatgpt.model');
        $this->temperature = config('laravel-ai-i18n.services.chatgpt.temperature');
    }

    /**
     * Translate text from source language to target language
     *
     * @param string $text Text to translate
     * @param string $sourceLanguage Source language code
     * @param string $targetLanguage Target language code
     * @param string $format Format of the text (json, php, plain)
     * @return string|null Translated text or null on failure
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage, string $format = 'plain'): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('ChatGPT API key is not set');
            return null;
        }

        try {
            $prompt = $this->buildPrompt($text, $sourceLanguage, $targetLanguage, $format);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional translator. Translate the text exactly as provided, maintaining all formatting, laravelAiI18ns, and structure.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => $this->temperature,
            ]);

            if ($response->successful()) {
                $result = $response->json('choices.0.message.content');
                return $this->cleanResponse($result, $format);
            } else {
                Log::error('ChatGPT API error: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('ChatGPT translation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build prompt for the ChatGPT API
     *
     * @param string $text Text to translate
     * @param string $sourceLanguage Source language code
     * @param string $targetLanguage Target language code
     * @param string $format Format of the text
     * @return string Prompt for the API
     */
    protected function buildPrompt(string $text, string $sourceLanguage, string $targetLanguage, string $format): string
    {
        $formatInstructions = match ($format) {
            'json' => "This is a JSON file. Maintain the exact JSON structure. Don't translate keys, only translate values. Keep all laravelAiI18ns like :laravelAiI18n, {laravelAiI18n}, etc. intact.",
            'php' => "This is a PHP array. Maintain the exact PHP array structure. Don't translate array keys, only translate values. Keep all laravelAiI18ns like :laravelAiI18n, {laravelAiI18n}, etc. intact.",
            default => "Translate the text. Keep all laravelAiI18ns like :laravelAiI18n, {laravelAiI18n}, etc. intact.",
        };

        return <<<PROMPT
Translate the following text from {$sourceLanguage} to {$targetLanguage}.
{$formatInstructions}

TEXT TO TRANSLATE:
{$text}
PROMPT;
    }

    /**
     * Clean the response from ChatGPT
     *
     * @param string $response Response from ChatGPT
     * @param string $format Format of the text
     * @return string Cleaned response
     */
    protected function cleanResponse(string $response, string $format): string
    {
        // Remove any markdown code blocks if present
        $response = preg_replace('/```(?:json|php)?\n(.*?)\n```/s', '$1', $response);

        // Remove any extra explanations that might be added
        if ($format === 'json' || $format === 'php') {
            // Try to extract just the code part if there's explanatory text
            if (preg_match('/(?:```(?:json|php)?\n)?((?:\{|\[|<\?php|return).*?)(?:\n```)?$/s', $response, $matches)) {
                $response = $matches[1];
            }
        }

        return trim($response);
    }
}
