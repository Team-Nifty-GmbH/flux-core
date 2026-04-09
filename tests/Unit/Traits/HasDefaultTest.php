<?php

use FluxErp\Models\Language;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use Illuminate\Support\Facades\Cache;

test('default returns the default model', function (): void {
    expect(Language::default())->not->toBeNull();
});

test('default returns null when no default exists', function (): void {
    PriceList::query()->update(['is_default' => false]);

    expect(PriceList::default())->toBeNull();
});

test('setting new default unsets previous default', function (): void {
    $first = VatRate::factory()->create(['is_default' => true]);
    $second = VatRate::factory()->create(['is_default' => true]);

    expect($first->fresh()->is_default)->toBeFalse();
    expect($second->fresh()->is_default)->toBeTrue();
});

test('default works with serializable_classes disabled', function (): void {
    // Simulate Laravel 13 default: no class unserialization allowed
    config()->set('cache.serializable_classes', false);
    config()->set('cache.default', 'file');
    Cache::store('file')->flush();
    Cache::forgetDriver('file');

    // First call caches the default as array (not model)
    $language = Language::default();
    expect($language)->not->toBeNull();

    // Flush memo so next call must deserialize from file cache
    Cache::forgetDriver('file');

    // With serializable_classes=false and a cached model object,
    // unserialize() returns __PHP_Incomplete_Class which causes
    // a TypeError on the return type ?static.
    // With the array fix, this works because arrays serialize safely.
    $languageFromCache = Language::default();
    expect($languageFromCache)->not->toBeNull()
        ->and($languageFromCache)->toBeInstanceOf(Language::class)
        ->and($languageFromCache->getKey())->toBe($language->getKey());
});

test('deleting default model invalidates cache', function (): void {
    $vatRate = VatRate::factory()->create(['is_default' => true]);

    expect(VatRate::default()->getKey())->toBe($vatRate->getKey());

    $vatRate->delete();

    expect(VatRate::default()?->getKey())->not->toBe($vatRate->getKey());
});
