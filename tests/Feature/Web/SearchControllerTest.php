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
