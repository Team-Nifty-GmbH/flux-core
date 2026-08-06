<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
});

test('the address route redirects to the contact', function (): void {
    $this->actingAs($this->user, 'web')
        ->get(route('address.id', ['address' => $this->address->getKey()]))
        ->assertRedirect(
            route('contacts.id?', [
                'id' => $this->contact->getKey(),
                'address' => $this->address->getKey(),
            ])
        );
});

test('the address route resolves by key even when the route key is customized', function (): void {
    $class = new class() extends Address
    {
        protected $table = 'addresses';

        public function getRouteKeyName(): string
        {
            return 'slug';
        }
    };
    $this->app->bind(Address::class, get_class($class));

    $this->actingAs($this->user, 'web')
        ->get(route('address.id', ['address' => $this->address->getKey()]))
        ->assertRedirect(
            route('contacts.id?', [
                'id' => $this->contact->getKey(),
                'address' => $this->address->getKey(),
            ])
        );
});

test('an unknown address still gives a 404', function (): void {
    $this->actingAs($this->user, 'web')
        ->get(route('address.id', ['address' => 999999]))
        ->assertNotFound();
});
