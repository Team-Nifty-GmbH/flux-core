<?php

use FluxErp\Models\Tenant;

it('combines name street postcode and city in postal_address_one_line', function (): void {
    $tenant = new Tenant([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => '12345',
        'city' => 'Example City',
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1 | 12345 Example City');
});

it('includes city after postcode in postal_address_one_line', function (): void {
    $tenant = new Tenant([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => '12345',
        'city' => 'Example City',
    ]);

    expect($tenant->postal_address_one_line)->toContain('12345 Example City');
});

it('omits postcode segment from postal_address_one_line when both postcode and city are empty', function (): void {
    $tenant = new Tenant([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1');
});

it('shows only postcode in postal_address_one_line when city is missing', function (): void {
    $tenant = new Tenant([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'postcode' => '12345',
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1 | 12345');
});

it('shows only city in postal_address_one_line when postcode is missing', function (): void {
    $tenant = new Tenant([
        'name' => 'Acme Corp',
        'street' => 'Example Street 1',
        'city' => 'Example City',
    ]);

    expect($tenant->postal_address_one_line)->toBe('Acme Corp | Example Street 1 | Example City');
});
