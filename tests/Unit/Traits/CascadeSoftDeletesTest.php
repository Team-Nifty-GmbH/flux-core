<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

test('deleting contact soft-deletes addresses', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    $contact->delete();

    expect(Address::query()->whereKey($address->getKey())->exists())->toBeFalse();
    expect(Address::withTrashed()->whereKey($address->getKey())->exists())->toBeTrue();
});

test('restoring contact restores addresses', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    $contact->delete();

    expect(Address::query()->whereKey($address->getKey())->exists())->toBeFalse();

    $contact->restore();

    expect(Address::query()->whereKey($address->getKey())->exists())->toBeTrue();
});
