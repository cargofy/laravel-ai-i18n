<?php

namespace Cargofy\LaravelAiI18n\Tests\Services;

use Cargofy\LaravelAiI18n\Services\ChatGptTranslationService;
use Cargofy\LaravelAiI18n\Services\TranslationServiceFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Configure ChatGPT service settings
    Config::set('laravel-ai-i18n.services.chatgpt.api_key', 'test-api-key');
    Config::set('laravel-ai-i18n.services.chatgpt.model', 'gpt-3.5-turbo');
    Config::set('laravel-ai-i18n.services.chatgpt.temperature', 0.3);
});

it('creates ChatGpt service by default', function () {
    // Configure settings
    Config::set('laravel-ai-i18n.driver', 'chatgpt');

    $service = TranslationServiceFactory::create();

    expect($service)->toBeInstanceOf(ChatGptTranslationService::class);
});

it('creates ChatGpt service when explicitly specified', function () {
    $service = TranslationServiceFactory::create('chatgpt');

    expect($service)->toBeInstanceOf(ChatGptTranslationService::class);
});

it('returns null for unsupported driver', function () {
    // Mock Log facade
    Log::shouldReceive('error')
        ->once()
        ->with('Unsupported translation driver: unsupported');

    $service = TranslationServiceFactory::create('unsupported');

    expect($service)->toBeNull();
});

it('uses driver from configuration if not explicitly specified', function () {
    // Configure settings
    Config::set('laravel-ai-i18n.driver', 'chatgpt');

    $service = TranslationServiceFactory::create();

    expect($service)->toBeInstanceOf(ChatGptTranslationService::class);
});
