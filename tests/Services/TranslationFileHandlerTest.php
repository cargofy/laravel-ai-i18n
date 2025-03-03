<?php

namespace Cargofy\LaravelAiI18n\Tests\Services;

use Cargofy\LaravelAiI18n\Services\TranslationFileHandler;
use Illuminate\Support\Facades\File;
use Mockery;
use ReflectionClass;
use Exception;

beforeEach(function () {
    // Create an instance of the class for testing
    $this->handler = new TranslationFileHandler();
});

it('determines file format by extension', function () {
    expect($this->handler->getFileFormat('file.json'))->toBe('json')
        ->and($this->handler->getFileFormat('file.php'))->toBe('php')
        ->and($this->handler->getFileFormat('file.txt'))->toBe('plain');
});

it('reads file content', function () {
    // Mock File facade
    File::shouldReceive('exists')
        ->once()
        ->with('path/to/file.php')
        ->andReturn(true);

    File::shouldReceive('get')
        ->once()
        ->with('path/to/file.php')
        ->andReturn('<?php return [];');

    $content = $this->handler->readFile('path/to/file.php');

    expect($content)->toBe('<?php return [];');
});

it('returns null if file does not exist', function () {
    // Mock File facade
    File::shouldReceive('exists')
        ->once()
        ->with('path/to/nonexistent.php')
        ->andReturn(false);

    $content = $this->handler->readFile('path/to/nonexistent.php');

    expect($content)->toBeNull();
});

it('writes content to file', function () {
    // Mock File facade
    File::shouldReceive('exists')
        ->once()
        ->with('path/to')
        ->andReturn(true);

    File::shouldReceive('put')
        ->once()
        ->with('path/to/file.php', '<?php return [];')
        ->andReturn(true);

    $result = $this->handler->writeFile('path/to/file.php', '<?php return [];');

    expect($result)->toBeTrue();
});

it('creates directory if it does not exist', function () {
    // Mock File facade
    File::shouldReceive('exists')
        ->once()
        ->with('path/to')
        ->andReturn(false);

    File::shouldReceive('makeDirectory')
        ->once()
        ->with('path/to', 0755, true)
        ->andReturn(true);

    File::shouldReceive('put')
        ->once()
        ->with('path/to/file.php', '<?php return [];')
        ->andReturn(true);

    $result = $this->handler->writeFile('path/to/file.php', '<?php return [];');

    expect($result)->toBeTrue();
});

it('returns false on write error', function () {
    // Mock File facade
    File::shouldReceive('exists')
        ->once()
        ->with('path/to')
        ->andReturn(true);

    File::shouldReceive('put')
        ->once()
        ->with('path/to/file.php', '<?php return [];')
        ->andThrow(new Exception('Write error'));

    $result = $this->handler->writeFile('path/to/file.php', '<?php return [];');

    expect($result)->toBeFalse();
});

it('correctly determines target file path for Laravel structure', function () {
    $sourcePath = 'resources/lang/en/messages.php';
    $targetPath = $this->handler->getTargetFilePath($sourcePath, 'en', 'uk');

    expect($targetPath)->toBe('resources/lang/uk/messages.php');
});

it('correctly determines target file path for JSON files', function () {
    $sourcePath = 'resources/lang/en.json';
    $targetPath = $this->handler->getTargetFilePath($sourcePath, 'en', 'uk');

    expect($targetPath)->toBe('resources/lang/uk.json');
});

it('correctly determines target file path for other cases', function () {
    $sourcePath = 'some/path/en-file.txt';
    $targetPath = $this->handler->getTargetFilePath($sourcePath, 'en', 'uk');

    expect($targetPath)->toBe('some/path/uk-file.txt');
});

it('finds all translation files', function () {
    // Mock File facade
    File::shouldReceive('exists')
        ->with('resources/lang')
        ->andReturn(true);

    File::shouldReceive('exists')
        ->with('resources/lang/en')
        ->andReturn(true);

    File::shouldReceive('isDirectory')
        ->with('resources/lang/en')
        ->andReturn(true);

    // Create mock for files
    $file1 = Mockery::mock();
    $file1->shouldReceive('getRelativePathname')->andReturn('messages.php');

    $file2 = Mockery::mock();
    $file2->shouldReceive('getRelativePathname')->andReturn('validation.php');

    File::shouldReceive('allFiles')
        ->with('resources/lang/en')
        ->andReturn([$file1, $file2]);

    File::shouldReceive('exists')
        ->with('resources/lang/en.json')
        ->andReturn(true);

    // Mock matchesPatterns method
    $handler = Mockery::mock(TranslationFileHandler::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $handler->shouldReceive('matchesPatterns')
        ->with('messages.php', ['*.php', '*.json'])
        ->andReturn(true);

    $handler->shouldReceive('matchesPatterns')
        ->with('messages.php', ['vendor/*'])
        ->andReturn(false);

    $handler->shouldReceive('matchesPatterns')
        ->with('validation.php', ['*.php', '*.json'])
        ->andReturn(true);

    $handler->shouldReceive('matchesPatterns')
        ->with('validation.php', ['vendor/*'])
        ->andReturn(false);

    $files = $handler->findTranslationFiles(['resources/lang'], ['*.php', '*.json'], ['vendor/*'], 'en');

    expect($files)->toHaveCount(3)
        ->and($files)->toContain('resources/lang/en/messages.php')
        ->and($files)->toContain('resources/lang/en/validation.php')
        ->and($files)->toContain('resources/lang/en.json');
});

it('checks if file path matches patterns', function () {
    $handler = new TranslationFileHandler();

    // Use reflection to access protected method
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('matchesPatterns');
    $method->setAccessible(true);

    // Check various patterns
    expect($method->invoke($handler, 'messages.php', ['*.php']))->toBeTrue()
        ->and($method->invoke($handler, 'messages.json', ['*.php']))->toBeFalse()
        ->and($method->invoke($handler, 'vendor/package/file.php', ['vendor/*']))->toBeTrue()
        ->and($method->invoke($handler, 'app/file.php', ['vendor/*']))->toBeFalse()
        ->and($method->invoke($handler, 'messages.php', ['*.php', '*.json']))->toBeTrue();
});
