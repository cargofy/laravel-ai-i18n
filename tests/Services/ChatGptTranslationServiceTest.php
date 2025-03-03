<?php

namespace Cargofy\LaravelAiI18n\Tests\Services;

use Cargofy\LaravelAiI18n\Services\ChatGptTranslationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Configure test settings
    Config::set('laravel-ai-i18n.services.chatgpt.api_key', 'test-api-key');
    Config::set('laravel-ai-i18n.services.chatgpt.model', 'gpt-3.5-turbo');
    Config::set('laravel-ai-i18n.services.chatgpt.temperature', 0.3);
});

it('successfully translates text', function () {
    // Mock HTTP request to ChatGPT API
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Translated text',
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new ChatGptTranslationService;
    $result = $service->translate('Text to translate', 'en', 'uk', 'plain');

    // Check the result
    expect($result)->toBe('Translated text');

    // Verify that the request was sent with correct parameters
    Http::assertSent(function ($request) {
        return $request->url() == 'https://api.openai.com/v1/chat/completions' &&
               $request->hasHeader('Authorization', 'Bearer test-api-key') &&
               $request->hasHeader('Content-Type', 'application/json') &&
               $request['model'] == 'gpt-3.5-turbo' &&
               $request['temperature'] == 0.3 &&
               $request['messages'][0]['role'] == 'system' &&
               $request['messages'][1]['role'] == 'user' &&
               str_contains($request['messages'][1]['content'], 'Text to translate');
    });
});

it('returns null on API error', function () {
    // Mock HTTP request with error
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'API Error',
            ],
        ], 400),
    ]);

    $service = new ChatGptTranslationService;
    $result = $service->translate('Text to translate', 'en', 'uk', 'plain');

    // Check that result is null on error
    expect($result)->toBeNull();
});

it('returns null when API key is missing', function () {
    // Configure settings without API key
    Config::set('laravel-ai-i18n.services.chatgpt.api_key', '');

    $service = new ChatGptTranslationService;
    $result = $service->translate('Text to translate', 'en', 'uk', 'plain');

    // Check that result is null when API key is missing
    expect($result)->toBeNull();
});

it('properly handles JSON format', function () {
    // Mock HTTP request for JSON format
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => '```json
{
  "hello": "Привіт",
  "world": "Світ"
}
```',
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new ChatGptTranslationService;
    $result = $service->translate('{"hello":"Hello","world":"World"}', 'en', 'uk', 'json');

    // Check that JSON is properly cleaned from markdown
    expect($result)->toBe('{
  "hello": "Привіт",
  "world": "Світ"
}');
});

it('properly handles PHP format', function () {
    // Mock HTTP request for PHP format
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => '```php
return [
  "hello" => "Привіт",
  "world" => "Світ"
];
```',
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new ChatGptTranslationService;
    $result = $service->translate('return ["hello" => "Hello", "world" => "World"];', 'en', 'uk', 'php');

    // Check that PHP code is properly cleaned from markdown
    expect($result)->toBe('return [
  "hello" => "Привіт",
  "world" => "Світ"
];');
});

it('handles exceptions during request', function () {
    // Mock HTTP request with exception
    Http::fake(function () {
        throw new \Exception('Connection error');
    });

    $service = new ChatGptTranslationService;
    $result = $service->translate('Text to translate', 'en', 'uk', 'plain');

    // Check that result is null on exception
    expect($result)->toBeNull();
});
