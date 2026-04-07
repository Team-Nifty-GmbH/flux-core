<?php

use FluxErp\Models\Currency;
use Illuminate\Support\Str;

test('model gets uuid on creation', function (): void {
    $currency = Currency::factory()->create();

    expect($currency->uuid)->not->toBeNull();
    expect(Str::isUuid($currency->uuid))->toBeTrue();
});

test('existing uuid is not overwritten', function (): void {
    $uuid = Str::uuid()->toString();
    $currency = Currency::factory()->create(['uuid' => $uuid]);

    expect($currency->uuid)->toBe($uuid);
});
