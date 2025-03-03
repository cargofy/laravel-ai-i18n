<?php

namespace Cargofy\LaravelAiI18n\Services;

use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected AbstractTranslationService $translator;

    protected TranslationFileHandler $fileHandler;

    protected bool $forceOverwrite = false;

    public function __construct(TranslationFileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
        $this->translator = TranslationServiceFactory::create();

        if ($this->translator === null) {
            throw new \RuntimeException('No translation service available');
        }
    }

    /**
     * Set whether to force overwrite existing translations
     *
     * @param  bool  $force  Whether to force overwrite
     */
    public function setForceOverwrite(bool $force): self
    {
        $this->forceOverwrite = $force;

        return $this;
    }

    /**
     * Set the translator service
     *
     * @param  AbstractTranslationService  $translator  Translator service
     */
    public function setTranslator(AbstractTranslationService $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Translate all files from source language to target languages
     *
     * @param  string  $sourceLanguage  Source language code
     * @param  array  $targetLanguages  Target language codes
     * @return array Translation statistics
     */
    public function translateAll(string $sourceLanguage, array $targetLanguages): array
    {
        $stats = [
            'total_files' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'by_language' => [],
        ];

        // Initialize stats for each target language
        foreach ($targetLanguages as $targetLanguage) {
            $stats['by_language'][$targetLanguage] = [
                'successful' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];
        }

        // Get configuration
        $langDirs = config('laravel-ai-i18n.paths.lang_dirs', []);
        $includePatterns = config('laravel-ai-i18n.paths.include_patterns', []);
        $excludePatterns = config('laravel-ai-i18n.paths.exclude_patterns', []);

        // Find all translation files
        $files = $this->fileHandler->findTranslationFiles($langDirs, $includePatterns, $excludePatterns, $sourceLanguage);
        $stats['total_files'] = count($files);

        // Translate each file to each target language
        foreach ($files as $sourceFilePath) {
            $fileFormat = $this->fileHandler->getFileFormat($sourceFilePath);
            $sourceContent = $this->fileHandler->readFile($sourceFilePath);

            if ($sourceContent === null) {
                Log::warning("Could not read source file: {$sourceFilePath}");
                $stats['failed']++;

                continue;
            }

            foreach ($targetLanguages as $targetLanguage) {
                $targetFilePath = $this->fileHandler->getTargetFilePath($sourceFilePath, $sourceLanguage, $targetLanguage);

                // Skip if target file exists and we're not forcing overwrite
                if (file_exists($targetFilePath) && ! $this->forceOverwrite) {
                    Log::info("Skipping existing file: {$targetFilePath}");
                    $stats['skipped']++;
                    $stats['by_language'][$targetLanguage]['skipped']++;

                    continue;
                }

                // Translate the content
                $translatedContent = $this->translator->translate($sourceContent, $sourceLanguage, $targetLanguage, $fileFormat);

                if ($translatedContent === null) {
                    Log::error("Failed to translate file: {$sourceFilePath} to {$targetLanguage}");
                    $stats['failed']++;
                    $stats['by_language'][$targetLanguage]['failed']++;

                    continue;
                }

                // Write the translated content to the target file
                $success = $this->fileHandler->writeFile($targetFilePath, $translatedContent);

                if ($success) {
                    Log::info("Successfully translated file: {$sourceFilePath} to {$targetFilePath}");
                    $stats['successful']++;
                    $stats['by_language'][$targetLanguage]['successful']++;
                } else {
                    Log::error("Failed to write translated file: {$targetFilePath}");
                    $stats['failed']++;
                    $stats['by_language'][$targetLanguage]['failed']++;
                }
            }
        }

        return $stats;
    }
}
