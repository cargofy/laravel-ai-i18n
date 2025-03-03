<?php

namespace Cargofy\LaravelAiI18n;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Cargofy\LaravelAiI18n\Commands\TranslateCommand;
use Cargofy\LaravelAiI18n\Services\AbstractTranslationService;
use Cargofy\LaravelAiI18n\Services\ChatGptTranslationService;
use Cargofy\LaravelAiI18n\Services\TranslationFileHandler;
use Cargofy\LaravelAiI18n\Services\TranslationService;
use Cargofy\LaravelAiI18n\Services\TranslationServiceFactory;

class LaravelAiI18nServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-ai-i18n')
            ->hasConfigFile()
            ->hasCommands([
                TranslateCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register the translation services
        $this->app->singleton(TranslationFileHandler::class, function ($app) {
            return new TranslationFileHandler();
        });

        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService(
                $app->make(TranslationFileHandler::class)
            );
        });
    }
}
