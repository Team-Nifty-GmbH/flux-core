<?php

use FluxErp\Models\Tenant;

test('combines name street postcode and city in postal_address_one_line', function (): void {
    $tenant = Tenant::factory()->create([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => '12345',
        'city' => 'Example City',
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1 | 12345 Example City');
});

test('omits postcode segment from postal_address_one_line when both postcode and city are empty', function (): void {
    $tenant = Tenant::factory()->create([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => null,
        'city' => null,
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1');
});

test('shows only postcode in postal_address_one_line when city is missing', function (): void {
    $tenant = Tenant::factory()->create([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => '12345',
        'city' => null,
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1 | 12345');
});

test('shows only city in postal_address_one_line when postcode is missing', function (): void {
    $tenant = Tenant::factory()->create([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => null,
        'city' => 'Example City',
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1 | Example City');
});
