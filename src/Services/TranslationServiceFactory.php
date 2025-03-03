<?php

namespace Cargofy\LaravelAiI18n\Services;

use Illuminate\Support\Facades\Log;

class TranslationServiceFactory
{
    /**
     * Create a translation service based on the driver
     *
     * @param  string|null  $driver  Driver name (default: from config)
     * @return AbstractTranslationService|null Translation service or null if driver is not supported
     */
    public static function create(?string $driver = null): ?AbstractTranslationService
    {
        $driver = $driver ?? config('laravel-ai-i18n.driver', 'chatgpt');

        return match ($driver) {
            'chatgpt' => new ChatGptTranslationService,
            // Add more drivers here in the future
            default => self::handleUnsupportedDriver($driver),
        };
    }

    /**
     * Handle unsupported driver
     *
     * @param  string  $driver  Driver name
     * @return null
     */
    private static function handleUnsupportedDriver(string $driver): ?AbstractTranslationService
    {
        Log::error("Unsupported translation driver: {$driver}");

        return null;
    }
}
