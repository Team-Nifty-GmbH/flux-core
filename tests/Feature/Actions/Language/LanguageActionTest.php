<?php

use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use FluxErp\Models\Language;

test('create language', function (): void {
    // The per-process seeded default language draws a random faker code and
    // occasionally lands on 'fr', which the unique rule then rejects.
    Language::query()->where('language_code', 'fr')->forceDelete();

    $language = CreateLanguage::make([
        'name' => 'Französisch',
        'iso_name' => 'French',
        'language_code' => 'fr',
    ])->validate()->execute();

    expect($language)
        ->toBeInstanceOf(Language::class)
        ->name->toBe('Französisch')
        ->language_code->toBe('fr');
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
