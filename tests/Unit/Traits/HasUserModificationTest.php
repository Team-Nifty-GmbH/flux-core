<?php

use FluxErp\Models\Currency;

test('created_by is set on creation', function (): void {
    $currency = Currency::factory()->create();

    expect($currency->created_by)->not->toBeNull();
});

test('updated_by is set on update', function (): void {
    $currency = Currency::factory()->create();
    $currency->update(['name' => 'Updated']);

    expect($currency->fresh()->updated_by)->not->toBeNull();
});

test('getCreatedByColumn returns correct column name', function (): void {
    $currency = Currency::factory()->create();

    expect($currency->getCreatedByColumn())->toBe('created_by');
    expect($currency->getUpdatedByColumn())->toBe('updated_by');
});
