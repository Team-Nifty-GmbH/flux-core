<?php

use FluxErp\Models\Language;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;

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

test('deleting default model invalidates cache', function (): void {
    $vatRate = VatRate::factory()->create(['is_default' => true]);

    expect(VatRate::default()->getKey())->toBe($vatRate->getKey());

    $vatRate->delete();

    expect(VatRate::default()?->getKey())->not->toBe($vatRate->getKey());
});
