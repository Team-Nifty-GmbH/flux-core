<?php

use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use FluxErp\Models\Language;

test('create language', function (): void {
    // 'zz' is not a valid ISO 639-1 code, so the default language created
    // in Pest.php via fake()->languageCode() can never collide with it.
    $language = CreateLanguage::make([
        'name' => 'Testsprache',
        'iso_name' => 'Testish',
        'language_code' => 'zz',
    ])->validate()->execute();

    expect($language)
        ->toBeInstanceOf(Language::class)
        ->name->toBe('Testsprache')
        ->language_code->toBe('zz');
});

test('create language requires name iso_name language_code', function (): void {
    CreateLanguage::assertValidationErrors([], ['name', 'iso_name', 'language_code']);
});

test('update language', function (): void {
    $language = Language::factory()->create();

    $updated = UpdateLanguage::make([
        'id' => $language->getKey(),
        'name' => 'Aktualisiert',
    ])->validate()->execute();

    expect($updated->name)->toBe('Aktualisiert');
});

test('delete language', function (): void {
    $language = Language::factory()->create();

    $result = DeleteLanguage::make([
        'id' => $language->getKey(),
    ])->validate()->execute();

    expect($result)->toBeTrue();
});
