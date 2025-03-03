<?php

namespace Cargofy\LaravelAiI18n\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PublishConfigTest extends TestCase
{
    /** @test */
    public function it_can_publish_config_file()
    {
        // Переконаємося, що файл конфігурації не існує
        if (File::exists(config_path('ai-i18n.php'))) {
            unlink(config_path('ai-i18n.php'));
        }

        $this->assertFalse(File::exists(config_path('ai-i18n.php')));

        // Публікуємо конфіг
        Artisan::call('vendor:publish', [
            '--tag' => 'laravel-ai-i18n-config',
        ]);

        // Перевіряємо, що файл конфігурації було опубліковано
        $this->assertTrue(File::exists(config_path('ai-i18n.php')));

        // Перевіряємо вміст опублікованого файлу
        $configContent = File::get(config_path('ai-i18n.php'));
        $this->assertStringContainsString('driver', $configContent);
        $this->assertStringContainsString('services', $configContent);
        $this->assertStringContainsString('languages', $configContent);
        $this->assertStringContainsString('paths', $configContent);

        // Видаляємо опублікований файл
        if (File::exists(config_path('ai-i18n.php'))) {
            unlink(config_path('ai-i18n.php'));
        }
    }

    /** @test */
    public function it_can_publish_service_provider()
    {
        // Переконаємося, що файл сервіс-провайдера не існує
        $providerPath = app_path('Providers/LaravelAiI18nServiceProvider.php');

        if (File::exists($providerPath)) {
            unlink($providerPath);
        }

        $this->assertFalse(File::exists($providerPath));

        // Публікуємо сервіс-провайдер
        Artisan::call('vendor:publish', [
            '--tag' => 'laravel-ai-i18n-provider',
        ]);

        // Перевіряємо, що файл сервіс-провайдера було опубліковано
        $this->assertTrue(File::exists($providerPath));

        // Перевіряємо вміст опублікованого файлу
        $providerContent = File::get($providerPath);
        $this->assertStringContainsString('LaravelAiI18nServiceProvider', $providerContent);

        // Видаляємо опублікований файл
        if (File::exists($providerPath)) {
            unlink($providerPath);
        }
    }
}
