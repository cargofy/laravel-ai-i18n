<?php

namespace Cargofy\LaravelAiI18n;

use Cargofy\LaravelAiI18n\Commands\TranslateCommand;
use Cargofy\LaravelAiI18n\Services\TranslationFileHandler;
use Cargofy\LaravelAiI18n\Services\TranslationService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasConfigFile('ai-i18n')
            ->publishesServiceProvider('LaravelAiI18nServiceProvider')
            ->hasCommands([
                TranslateCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register the translation services
        $this->app->singleton(TranslationFileHandler::class, function ($app) {
            return new TranslationFileHandler;
        });

        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService(
                $app->make(TranslationFileHandler::class)
            );
        });
    }

    public function packageBooted(): void
    {
        // Публікація конфігураційного файлу
        $this->publishes([
            __DIR__.'/../config/ai-i18n.php' => config_path('ai-i18n.php'),
        ], 'laravel-ai-i18n-config');

        // Публікація сервіс-провайдера
        $this->publishes([
            __DIR__.'/LaravelAiI18nServiceProvider.php' => app_path('Providers/LaravelAiI18nServiceProvider.php'),
        ], 'laravel-ai-i18n-provider');
    }
}
