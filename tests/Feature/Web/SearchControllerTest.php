<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Product;

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

test('search controller strips markup from the strings it returns', function (): void {
    $product = Product::factory()->create([
        'name' => 'Widget',
        'description' => '<p style="text-align: left;">Erste Zeile&nbsp;&amp; mehr</p><p>zweite</p>',
    ]);

    $response = $this->post(
        route('search', Product::class),
        ['selected' => [$product->getKey()]]
    );

    $response->assertOk();

    $description = data_get($response->json(), '0.description');

    expect($description)->not->toContain('<')
        ->and($description)->not->toContain('&amp;')
        ->and($description)->toContain('Erste Zeile')
        ->and($description)->toContain('& mehr');
});

test('search controller shortens a long string and keeps the image url whole', function (): void {
    $product = Product::factory()->create([
        'description' => str_repeat('sehr lange beschreibung ', 40),
    ]);

    $response = $this->post(
        route('search', Product::class),
        ['selected' => [$product->getKey()]]
    );

    $response->assertOk();

    $result = data_get($response->json(), '0');

    expect(mb_strlen(data_get($result, 'description')))->toBeLessThanOrEqual(104)
        ->and(data_get($result, 'description'))->toEndWith('...')
        ->and(data_get($result, 'image'))->toBe($product->getAvatarUrl());
});
