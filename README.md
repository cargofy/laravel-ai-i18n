# This is my package laravel-ai-i18n

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cargofy/laravel-ai-i18n.svg?style=flat-square)](https://packagist.org/packages/cargofy/laravel-ai-i18n)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cargofy/laravel-ai-i18n/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cargofy/laravel-ai-i18n/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cargofy/laravel-ai-i18n/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cargofy/laravel-ai-i18n/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cargofy/laravel-ai-i18n.svg?style=flat-square)](https://packagist.org/packages/cargofy/laravel-ai-i18n)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-ai-i18n.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-ai-i18n)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require cargofy/laravel-ai-i18n
```

## Публікація ресурсів

Ви можете опублікувати конфігураційний файл за допомогою:

```bash
php artisan vendor:publish --tag="laravel-ai-i18n-config"
```

Ви також можете опублікувати сервіс-провайдер для подальшої кастомізації:

```bash
php artisan vendor:publish --tag="laravel-ai-i18n-provider"
```

Ось вміст опублікованого конфігураційного файлу:

```php
return [
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
```

## Usage

### AI Translation

This package provides a command to translate your language files using ChatGPT. The command will translate all language files from the source language to the target languages.

#### Configuration

First, make sure you have set up your OpenAI API key in your `.env` file:

```
OPENAI_API_KEY=your-api-key
```

You can also configure which translation service to use:

```
AI_TRANSLATION_DRIVER=chatgpt
```

The source and target languages are configured directly in the config file. You can modify them by publishing the config file and editing the `languages` section.

#### Running the Translation Command

To translate all language files:

```bash
php artisan translate:ai
```

You can also specify the source and target languages directly in the command:

```bash
php artisan translate:ai --source=en --target=uk,de,fr
```

By default, the command will skip files that already exist. If you want to force overwrite existing translations, use the `--force` option:

```bash
php artisan translate:ai --force
```

#### Supported File Formats

The translation command supports the following file formats:
- JSON language files (e.g., `en.json`)
- PHP language files (e.g., `resources/lang/en/messages.php`)

The translator will maintain the structure of the original files, only translating the values and preserving all laravelAiI18ns and placeholders.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alex Kovalchuk](https://github.com/cargofy)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
