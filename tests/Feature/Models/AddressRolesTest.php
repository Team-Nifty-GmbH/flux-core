<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

// The roles of a contact are single assignment: an address does not give one up
// by being switched off, it loses it when another address takes it over. A
// deleted address hands its roles to the one that survives it, but it used to
// keep claiming them on its own row, so restoring it left the contact with two
// main addresses and no way back, because both toggles lock once they are on.
test('a deleted address gives up the roles it hands over', function (): void {
    $contact = Contact::factory()->create();
    $main = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_delivery_address' => true,
        'is_invoice_address' => false,
    ]);
    $second = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => false,
        'is_delivery_address' => false,
        'is_invoice_address' => true,
    ]);

    $main->delete();

    expect(Address::withTrashed()->whereKey($main->getKey())->first())
        ->is_main_address->toBeFalse()
        ->is_delivery_address->toBeFalse()
        ->and(Address::query()->whereKey($second->getKey())->first())
        ->is_main_address->toBeTrue()
        ->and($contact->fresh()->main_address_id)->toBe($second->getKey());
});

test('restoring a deleted address leaves the contact with a single main address', function (): void {
    $contact = Contact::factory()->create();
    $main = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_delivery_address' => true,
        'is_invoice_address' => false,
    ]);
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => false,
        'is_delivery_address' => false,
        'is_invoice_address' => true,
    ]);

    $main->delete();
    Address::onlyTrashed()->whereKey($main->getKey())->first()->restore();

    expect(Address::query()->where('contact_id', $contact->getKey())->where('is_main_address', true)->count())
        ->toBe(1);
});

test('restoring a contact leaves it with a single main address', function (): void {
    $contact = Contact::factory()->create();
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_delivery_address' => true,
        'is_invoice_address' => false,
    ]);
    Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => false,
        'is_delivery_address' => false,
        'is_invoice_address' => true,
    ]);

    $contact->delete();
    Contact::onlyTrashed()->whereKey($contact->getKey())->first()->restore();

    expect(Address::query()->where('contact_id', $contact->getKey())->where('is_main_address', true)->count())
        ->toBe(1)
        ->and(Address::query()->where('contact_id', $contact->getKey())->where('is_delivery_address', true)->count())
        ->toBe(1);
});
