<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Settings
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for the translation service.
    |
    */

    // The driver to use for translation
    'driver' => env('AI_TRANSLATION_DRIVER', 'chatgpt'),

    // Available translation services
    'services' => [
        // ChatGPT translation service
        'chatgpt' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'temperature' => env('OPENAI_TEMPERATURE', 0.3),
        ],

        // You can add more services here in the future
        // 'google' => [
        //     'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        // ],
    ],

    // Language settings
    'languages' => [
        // The source language code (ISO 639-1)
        'source' => 'en',

        // Target languages to translate to (array of ISO 639-1 codes)
        'targets' => ['uk', 'de', 'fr', 'es'],
    ],

    // Paths configuration
    'paths' => [
        // Directories containing language files to translate
        'lang_dirs' => [
            'lang',
            'resources/lang',
        ],

        // File patterns to include in translation
        'include_patterns' => [
            '*.json',
            '*.php',
        ],

        // File patterns to exclude from translation
        'exclude_patterns' => [
            'vendor/**',
            'node_modules/**',
        ],
    ],
];
