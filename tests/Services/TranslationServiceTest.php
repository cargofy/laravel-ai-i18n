<?php

namespace Cargofy\LaravelAiI18n\Tests\Services;

use Cargofy\LaravelAiI18n\Services\AbstractTranslationService;
use Cargofy\LaravelAiI18n\Services\TranslationFileHandler;
use Cargofy\LaravelAiI18n\Services\TranslationService;
use Illuminate\Support\Facades\Config;
use Mockery;

beforeEach(function () {
    // Configure test settings
    Config::set('laravel-ai-i18n.paths.lang_dirs', ['resources/lang']);
    Config::set('laravel-ai-i18n.paths.include_patterns', ['*.php', '*.json']);
    Config::set('laravel-ai-i18n.paths.exclude_patterns', ['vendor/*']);

    // Configure ChatGPT service settings
    Config::set('laravel-ai-i18n.services.chatgpt.api_key', 'test-api-key');
    Config::set('laravel-ai-i18n.services.chatgpt.model', 'gpt-3.5-turbo');
    Config::set('laravel-ai-i18n.services.chatgpt.temperature', 0.3);
});

it('successfully translates all files', function () {
    // Mock TranslationFileHandler
    $fileHandler = Mockery::mock(TranslationFileHandler::class);

    // Configure mock for finding files
    $fileHandler->shouldReceive('findTranslationFiles')
        ->once()
        ->with(['resources/lang'], ['*.php', '*.json'], ['vendor/*'], 'en')
        ->andReturn(['resources/lang/en/messages.php', 'resources/lang/en.json']);

    // Configure mock for reading files
    $fileHandler->shouldReceive('readFile')
        ->with('resources/lang/en/messages.php')
        ->andReturn('<?php return ["hello" => "Hello", "world" => "World"];');

    $fileHandler->shouldReceive('readFile')
        ->with('resources/lang/en.json')
        ->andReturn('{"welcome": "Welcome"}');

    // Configure mock for getting file formats
    $fileHandler->shouldReceive('getFileFormat')
        ->with('resources/lang/en/messages.php')
        ->andReturn('php');

    $fileHandler->shouldReceive('getFileFormat')
        ->with('resources/lang/en.json')
        ->andReturn('json');

    // Configure mock for getting target file paths
    $fileHandler->shouldReceive('getTargetFilePath')
        ->with('resources/lang/en/messages.php', 'en', 'uk')
        ->andReturn('resources/lang/uk/messages.php');

    $fileHandler->shouldReceive('getTargetFilePath')
        ->with('resources/lang/en.json', 'en', 'uk')
        ->andReturn('resources/lang/uk.json');

    // Configure mock for writing files
    $fileHandler->shouldReceive('writeFile')
        ->with('resources/lang/uk/messages.php', '<?php return ["hello" => "Привіт", "world" => "Світ"];')
        ->andReturn(true);

    $fileHandler->shouldReceive('writeFile')
        ->with('resources/lang/uk.json', '{"welcome": "Ласкаво просимо"}')
        ->andReturn(true);

    // Mock translation service
    $translator = Mockery::mock(AbstractTranslationService::class);

    // Configure mock for translation
    $translator->shouldReceive('translate')
        ->with('<?php return ["hello" => "Hello", "world" => "World"];', 'en', 'uk', 'php')
        ->andReturn('<?php return ["hello" => "Привіт", "world" => "Світ"];');

    $translator->shouldReceive('translate')
        ->with('{"welcome": "Welcome"}', 'en', 'uk', 'json')
        ->andReturn('{"welcome": "Ласкаво просимо"}');

    // Create translation service with mocks
    $service = new TranslationService($fileHandler);
    $service->setTranslator($translator);

    // Call translation method
    $stats = $service->translateAll('en', ['uk']);

    // Check statistics
    expect($stats['total_files'])->toBe(2)
        ->and($stats['successful'])->toBe(2)
        ->and($stats['failed'])->toBe(0)
        ->and($stats['skipped'])->toBe(0)
        ->and($stats['by_language']['uk']['successful'])->toBe(2);
});

it('skips existing files if forceOverwrite is not set', function () {
    // Mock TranslationFileHandler
    $fileHandler = Mockery::mock(TranslationFileHandler::class);

    // Configure mock for finding files
    $fileHandler->shouldReceive('findTranslationFiles')
        ->once()
        ->andReturn(['resources/lang/en/messages.php']);

    // Configure mock for reading files
    $fileHandler->shouldReceive('readFile')
        ->with('resources/lang/en/messages.php')
        ->andReturn('<?php return ["hello" => "Hello"];');

    // Configure mock for getting file formats
    $fileHandler->shouldReceive('getFileFormat')
        ->with('resources/lang/en/messages.php')
        ->andReturn('php');

    // Configure mock for getting target file paths
    $fileHandler->shouldReceive('getTargetFilePath')
        ->with('resources/lang/en/messages.php', 'en', 'uk')
        ->andReturn('resources/lang/uk/messages.php');

    // Mock file existence
    $fileHandler->shouldReceive('writeFile')->never();

    // Mock translation service
    $translator = Mockery::mock(AbstractTranslationService::class);
    $translator->shouldReceive('translate')->never();

    // Create translation service with mocks
    $service = new TranslationService($fileHandler);
    $service->setTranslator($translator);

    // Mock file_exists function to return true for the target file
    $this->instance('file_exists', function ($path) {
        return $path === 'resources/lang/uk/messages.php';
    });

    // Call translation method
    $stats = $service->translateAll('en', ['uk']);

    // Check statistics
    expect($stats['total_files'])->toBe(1)
        ->and($stats['successful'])->toBe(0)
        ->and($stats['failed'])->toBe(0)
        ->and($stats['skipped'])->toBe(1)
        ->and($stats['by_language']['uk']['skipped'])->toBe(1);
});

it('overwrites existing files if forceOverwrite is set', function () {
    // Mock TranslationFileHandler
    $fileHandler = Mockery::mock(TranslationFileHandler::class);

    // Configure mock for finding files
    $fileHandler->shouldReceive('findTranslationFiles')
        ->once()
        ->andReturn(['resources/lang/en/messages.php']);

    // Configure mock for reading files
    $fileHandler->shouldReceive('readFile')
        ->with('resources/lang/en/messages.php')
        ->andReturn('<?php return ["hello" => "Hello"];');

    // Configure mock for getting file formats
    $fileHandler->shouldReceive('getFileFormat')
        ->with('resources/lang/en/messages.php')
        ->andReturn('php');

    // Configure mock for getting target file paths
    $fileHandler->shouldReceive('getTargetFilePath')
        ->with('resources/lang/en/messages.php', 'en', 'uk')
        ->andReturn('resources/lang/uk/messages.php');

    // Configure mock for writing files
    $fileHandler->shouldReceive('writeFile')
        ->with('resources/lang/uk/messages.php', '<?php return ["hello" => "Привіт"];')
        ->andReturn(true);

    // Mock translation service
    $translator = Mockery::mock(AbstractTranslationService::class);

    // Configure mock for translation
    $translator->shouldReceive('translate')
        ->with('<?php return ["hello" => "Hello"];', 'en', 'uk', 'php')
        ->andReturn('<?php return ["hello" => "Привіт"];');

    // Create translation service with mocks
    $service = new TranslationService($fileHandler);
    $service->setTranslator($translator);
    $service->setForceOverwrite(true);

    // Mock file_exists function to return true for the target file
    $this->instance('file_exists', function ($path) {
        return $path === 'resources/lang/uk/messages.php';
    });

    // Call translation method
    $stats = $service->translateAll('en', ['uk']);

    // Check statistics
    expect($stats['total_files'])->toBe(1)
        ->and($stats['successful'])->toBe(1)
        ->and($stats['failed'])->toBe(0)
        ->and($stats['skipped'])->toBe(0)
        ->and($stats['by_language']['uk']['successful'])->toBe(1);
});

it('handles errors during translation', function () {
    // Mock TranslationFileHandler
    $fileHandler = Mockery::mock(TranslationFileHandler::class);

    // Configure mock for finding files
    $fileHandler->shouldReceive('findTranslationFiles')
        ->once()
        ->andReturn(['resources/lang/en/messages.php']);

    // Configure mock for reading files
    $fileHandler->shouldReceive('readFile')
        ->with('resources/lang/en/messages.php')
        ->andReturn('<?php return ["hello" => "Hello"];');

    // Configure mock for getting file formats
    $fileHandler->shouldReceive('getFileFormat')
        ->with('resources/lang/en/messages.php')
        ->andReturn('php');

    // Configure mock for getting target file paths
    $fileHandler->shouldReceive('getTargetFilePath')
        ->with('resources/lang/en/messages.php', 'en', 'uk')
        ->andReturn('resources/lang/uk/messages.php');

    // Mock translation service with error
    $translator = Mockery::mock(AbstractTranslationService::class);

    // Configure mock for translation with error
    $translator->shouldReceive('translate')
        ->with('<?php return ["hello" => "Hello"];', 'en', 'uk', 'php')
        ->andReturn(null);

    // Create translation service with mocks
    $service = new TranslationService($fileHandler);
    $service->setTranslator($translator);

    // Call translation method
    $stats = $service->translateAll('en', ['uk']);

    // Check statistics
    expect($stats['total_files'])->toBe(1)
        ->and($stats['successful'])->toBe(0)
        ->and($stats['failed'])->toBe(1)
        ->and($stats['skipped'])->toBe(0)
        ->and($stats['by_language']['uk']['failed'])->toBe(1);
});

it('handles errors when writing files', function () {
    // Mock TranslationFileHandler
    $fileHandler = Mockery::mock(TranslationFileHandler::class);

    // Configure mock for finding files
    $fileHandler->shouldReceive('findTranslationFiles')
        ->once()
        ->andReturn(['resources/lang/en/messages.php']);

    // Configure mock for reading files
    $fileHandler->shouldReceive('readFile')
        ->with('resources/lang/en/messages.php')
        ->andReturn('<?php return ["hello" => "Hello"];');

    // Configure mock for getting file formats
    $fileHandler->shouldReceive('getFileFormat')
        ->with('resources/lang/en/messages.php')
        ->andReturn('php');

    // Configure mock for getting target file paths
    $fileHandler->shouldReceive('getTargetFilePath')
        ->with('resources/lang/en/messages.php', 'en', 'uk')
        ->andReturn('resources/lang/uk/messages.php');

    // Configure mock for writing files with error
    $fileHandler->shouldReceive('writeFile')
        ->with('resources/lang/uk/messages.php', '<?php return ["hello" => "Привіт"];')
        ->andReturn(false);

    // Mock translation service
    $translator = Mockery::mock(AbstractTranslationService::class);

    // Configure mock for translation
    $translator->shouldReceive('translate')
        ->with('<?php return ["hello" => "Hello"];', 'en', 'uk', 'php')
        ->andReturn('<?php return ["hello" => "Привіт"];');

    // Create translation service with mocks
    $service = new TranslationService($fileHandler);
    $service->setTranslator($translator);

    // Call translation method
    $stats = $service->translateAll('en', ['uk']);

    // Check statistics
    expect($stats['total_files'])->toBe(1)
        ->and($stats['successful'])->toBe(0)
        ->and($stats['failed'])->toBe(1)
        ->and($stats['skipped'])->toBe(0)
        ->and($stats['by_language']['uk']['failed'])->toBe(1);
});
