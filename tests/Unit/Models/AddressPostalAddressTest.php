<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Tenant;

it('includes both firstname and lastname in postal_address', function (): void {
    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Acme Corp',
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'street' => 'Example Street 1',
        'zip' => '12345',
        'city' => 'Example City',
    ]);

    expect($address->postal_address)->toContain('Jane Doe');
});

it('combines zip and city in postal_address', function (): void {
    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Acme Corp',
        'firstname' => null,
        'lastname' => null,
        'street' => 'Example Street 1',
        'zip' => '12345',
        'city' => 'Example City',
    ]);

    expect($address->postal_address)->toContain('12345 Example City');
});

it('falls back to lastname only when firstname is missing', function (): void {
    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Acme Corp',
        'firstname' => null,
        'lastname' => 'Doe',
        'street' => 'Example Street 1',
        'zip' => '12345',
        'city' => 'Example City',
    ]);

    expect($address->postal_address)->toContain('Doe');
});
