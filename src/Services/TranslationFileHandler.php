<?php

namespace Cargofy\LaravelAiI18n\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TranslationFileHandler
{
    /**
     * Get the format of a file based on its extension
     *
     * @param  string  $filePath  Path to the file
     * @return string Format of the file (json, php, plain)
     */
    public function getFileFormat(string $filePath): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match ($extension) {
            'json' => 'json',
            'php' => 'php',
            default => 'plain',
        };
    }

    /**
     * Read the content of a file
     *
     * @param  string  $filePath  Path to the file
     * @return string|null Content of the file or null on failure
     */
    public function readFile(string $filePath): ?string
    {
        if (! File::exists($filePath)) {
            return null;
        }

        return File::get($filePath);
    }

    /**
     * Write content to a file
     *
     * @param  string  $filePath  Path to the file
     * @param  string  $content  Content to write
     * @return bool True on success, false on failure
     */
    public function writeFile(string $filePath, string $content): bool
    {
        try {
            // Ensure the directory exists
            $directory = dirname($filePath);
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($filePath, $content);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the target file path for a translation
     *
     * @param  string  $sourceFilePath  Source file path
     * @param  string  $sourceLanguage  Source language code
     * @param  string  $targetLanguage  Target language code
     * @return string Target file path
     */
    public function getTargetFilePath(string $sourceFilePath, string $sourceLanguage, string $targetLanguage): string
    {
        // For language files in Laravel structure (e.g., resources/lang/en/messages.php)
        if (preg_match('#(.*?)/'.$sourceLanguage.'/([^/]+)$#', $sourceFilePath, $matches)) {
            return $matches[1].'/'.$targetLanguage.'/'.$matches[2];
        }

        // For JSON language files (e.g., resources/lang/en.json)
        if (Str::endsWith($sourceFilePath, $sourceLanguage.'.json')) {
            return Str::replaceLast($sourceLanguage.'.json', $targetLanguage.'.json', $sourceFilePath);
        }

        // Default: replace language code in the filename
        return preg_replace('#('.$sourceLanguage.')([^a-z0-9]|$)#i', $targetLanguage.'$2', $sourceFilePath);
    }

    /**
     * Find all translation files in the given directories
     *
     * @param  array  $directories  Directories to search in
     * @param  array  $includePatterns  Patterns to include
     * @param  array  $excludePatterns  Patterns to exclude
     * @param  string  $sourceLanguage  Source language code
     * @return array Array of file paths
     */
    public function findTranslationFiles(array $directories, array $includePatterns, array $excludePatterns, string $sourceLanguage): array
    {
        $files = [];

        foreach ($directories as $directory) {
            if (! File::exists($directory)) {
                continue;
            }

            // Look for language files in the source language directory
            $langDir = $directory.'/'.$sourceLanguage;
            if (File::exists($langDir) && File::isDirectory($langDir)) {
                // Find all files in the language directory
                $dirFiles = File::allFiles($langDir);
                foreach ($dirFiles as $file) {
                    $relativePath = $file->getRelativePathname();
                    $fullPath = $langDir.'/'.$relativePath;

                    // Check if the file matches include patterns and doesn't match exclude patterns
                    if ($this->matchesPatterns($relativePath, $includePatterns) && ! $this->matchesPatterns($relativePath, $excludePatterns)) {
                        $files[] = $fullPath;
                    }
                }
            }

            // Look for JSON language files (e.g., en.json)
            $jsonFile = $directory.'/'.$sourceLanguage.'.json';
            if (File::exists($jsonFile)) {
                $files[] = $jsonFile;
            }
        }

        return $files;
    }

    /**
     * Check if a file path matches any of the given patterns
     *
     * @param  string  $filePath  File path to check
     * @param  array  $patterns  Patterns to match against
     * @return bool True if the file path matches any pattern, false otherwise
     */
    protected function matchesPatterns(string $filePath, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $filePath)) {
                return true;
            }
        }

        return false;
    }
}
