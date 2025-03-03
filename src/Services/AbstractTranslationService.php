<?php

namespace Cargofy\LaravelAiI18n\Services;

abstract class AbstractTranslationService
{
    /**
     * Translate text from source language to target language
     *
     * @param  string  $text  Text to translate
     * @param  string  $sourceLanguage  Source language code
     * @param  string  $targetLanguage  Target language code
     * @param  string  $format  Format of the text (json, php, plain)
     * @return string|null Translated text or null on failure
     */
    abstract public function translate(string $text, string $sourceLanguage, string $targetLanguage, string $format = 'plain'): ?string;
}
