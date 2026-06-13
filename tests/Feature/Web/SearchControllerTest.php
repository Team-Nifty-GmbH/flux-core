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

test('search controller maps response keys to the requested mapping', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'recipient@example.com',
        'is_main_address' => true,
    ]);

    $response = $this->get(route('search', Address::class) . '?' . http_build_query([
        'search' => 'recipient@example.com',
        'searchFields' => ['email_primary', 'name'],
        'fields' => ['email_primary'],
        'mapping' => ['value' => 'email_primary', 'description' => 'label'],
    ]));

    $response->assertOk();

    $item = collect($response->json())->firstWhere('value', 'recipient@example.com');

    expect($item)->not->toBeNull()
        ->and(data_get($item, 'description'))->toBe($address->getLabel());
});
