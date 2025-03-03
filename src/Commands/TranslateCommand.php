<?php

namespace Cargofy\LaravelAiI18n\Commands;

use Cargofy\LaravelAiI18n\Services\TranslationService;
use Illuminate\Console\Command;

class TranslateCommand extends Command
{
    public $signature = 'translate:ai
                        {--source= : Source language code (default from config)}
                        {--target= : Target language codes (comma-separated, default from config)}
                        {--force : Force overwrite of existing translations}';

    public $description = 'Translate language files using AI';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle(): int
    {
        $this->info('Starting AI translation...');

        // Get source and target languages
        $sourceLanguage = $this->option('source') ?? config('laravel-ai-i18n.languages.source');
        $targetLanguagesOption = $this->option('target');
        $forceOverwrite = $this->option('force') ?? false;

        if ($targetLanguagesOption) {
            $targetLanguages = explode(',', $targetLanguagesOption);
        } else {
            $targetLanguages = config('laravel-ai-i18n.languages.targets', []);
        }

        if (empty($sourceLanguage)) {
            $this->error('Source language is not specified. Please set it in the config or use the --source option.');

            return self::FAILURE;
        }

        if (empty($targetLanguages)) {
            $this->error('Target languages are not specified. Please set them in the config or use the --target option.');

            return self::FAILURE;
        }

        $this->info("Source language: {$sourceLanguage}");
        $this->info('Target languages: '.implode(', ', $targetLanguages));
        if ($forceOverwrite) {
            $this->warn('Force overwrite is enabled. Existing translations will be overwritten.');
        }

        // Check if API key is set for the selected driver
        $driver = config('laravel-ai-i18n.driver', 'chatgpt');
        if (empty(config("laravel-ai-i18n.services.{$driver}.api_key"))) {
            $this->error("API key for {$driver} is not set. Please set it in the config or .env file.");

            return self::FAILURE;
        }

        // Start translation with progress bar
        $this->info('Searching for translation files...');

        // Set force overwrite option
        $this->translationService->setForceOverwrite($forceOverwrite);

        // Translate all files
        $stats = $this->translationService->translateAll($sourceLanguage, $targetLanguages);

        // Display results
        $this->info('Translation completed!');
        $this->info("Total files: {$stats['total_files']}");
        $this->info("Successfully translated: {$stats['successful']}");
        $this->info("Failed: {$stats['failed']}");
        $this->info("Skipped: {$stats['skipped']}");

        $this->info('Results by language:');
        foreach ($stats['by_language'] as $language => $langStats) {
            $this->info("  {$language}: {$langStats['successful']} successful, {$langStats['failed']} failed, {$langStats['skipped']} skipped");
        }

        if ($stats['failed'] > 0) {
            $this->warn('Some translations failed. Check the logs for more details.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
