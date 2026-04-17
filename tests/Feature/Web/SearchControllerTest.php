<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

test('search controller returns soft deleted record when selected', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $address->delete();

    $this->assertSoftDeleted($address);

    $response = $this->post(
        route('search', Address::class),
        ['selected' => [$address->getKey()]]
    );

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['id' => $address->getKey()]);
});

test('search controller returns multiple selected records including soft deleted', function (): void {
    $contact = Contact::factory()->create();

    $activeAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $softDeletedAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $softDeletedAddress->delete();

    $this->assertSoftDeleted($softDeletedAddress);

    $response = $this->post(
        route('search', Address::class),
        ['selected' => [$activeAddress->getKey(), $softDeletedAddress->getKey()]]
    );

    $response->assertOk();
    $response->assertJsonCount(2);
    $response->assertJsonFragment(['id' => $activeAddress->getKey()]);
    $response->assertJsonFragment(['id' => $softDeletedAddress->getKey()]);
});
