<?php

use FluxErp\Enums\SalutationEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Tenant;

it('uses the address language for the salutation translation', function (): void {
    app()->setLocale('de');

    $english = Language::factory()->create([
        'language_code' => 'en',
        'iso_name' => 'en',
        'name' => 'English',
    ]);

    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => $english->getKey(),
        'salutation' => SalutationEnum::Mrs,
        'lastname' => 'Lopez',
        'has_formal_salutation' => true,
    ]);

    expect($address->salutation())->toBe('Dear Mrs. Lopez');
});

it('falls back to the current locale when address has no language', function (): void {
    app()->setLocale('de');

    $tenant = Tenant::factory()->create();
    $contact = Contact::factory()
        ->hasAttached($tenant, relationship: 'tenants')
        ->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'language_id' => null,
        'salutation' => SalutationEnum::Mrs,
        'lastname' => 'Lopez',
        'has_formal_salutation' => true,
    ]);

    expect($address->salutation())->toBe('Sehr geehrte Frau Lopez');
});
