<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Tenant;

test('reloads a relation that was cached before the related record existed', function (): void {
    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create(['main_address_id' => null]);

    expect($contact->toSearchableArray()['main_address'])->toBeNull();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'company' => 'Acme Corp',
        'firstname' => 'Jane',
        'lastname' => 'Doe',
    ]);

    $contact->main_address_id = $address->getKey();
    $contact->save();

    expect(data_get($contact->toSearchableArray(), 'main_address.lastname'))->toBe('Doe');
});

test('reloads attributes changed outside of the model instance', function (): void {
    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create(['header' => 'before']);

    Contact::query()
        ->whereKey($contact->getKey())
        ->update(['header' => 'after']);

    expect($contact->toSearchableArray()['header'])->toBe('after');
});
