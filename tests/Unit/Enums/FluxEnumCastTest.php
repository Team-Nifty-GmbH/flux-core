<?php

use FluxErp\Models\Address;

test('cast returns enum object for known value', function (): void {
    $address = Address::factory()->make(['salutation' => 'mr']);

    expect($address->salutation)->not->toBeNull()
        ->and($address->salutation->value)->toBe('mr')
        ->and($address->salutation->name)->toBe('Mr');
});

test('cast returns null for null value', function (): void {
    $address = Address::factory()->make(['salutation' => null]);

    expect($address->salutation)->toBeNull();
});

test('cast returns fallback object for unknown value', function (): void {
    $address = Address::factory()->make();
    $address->setRawAttributes(array_merge($address->getAttributes(), ['salutation' => 'custom_value']));

    expect($address->salutation)->not->toBeNull()
        ->and($address->salutation->value)->toBe('custom_value');
});
