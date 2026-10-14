<?php

use Dom\HTMLDocument;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;

beforeEach(function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_default' => false,
        ]);

    $this->contact = Contact::factory()->create([
        'payment_type_id' => $paymentType->id,
    ]);

    Address::factory()->create([
        'contact_id' => $this->contact->id,
        'is_main_address' => true,
    ]);
});

test('contacts id contact not found', function (): void {
    $this->contact->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/' . $this->contact->id)
        ->assertNotFound();
});

test('contacts id no user', function (): void {
    $this->actingAsGuest();

    $this->get('/contacts/contacts/' . $this->contact->id)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('contacts id page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/contacts/' . $this->contact->id)
        ->assertOk();
});

test('contacts id page without id', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/contacts/0')
        ->assertNotFound();
});

test('contacts id without permission', function (): void {
    Permission::findOrCreate('contacts.{id?}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/contacts/contacts/' . $this->contact->id)
        ->assertForbidden();
});

test('contacts no user', function (): void {
    $this->actingAsGuest();

    $this->get('/contacts/contacts')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('contacts page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/contacts/contacts')
        ->assertOk();
});

test('contacts without permission', function (): void {
    Permission::findOrCreate('contacts.get', 'web');

    $this->actingAs($this->user, 'web')->get('/contacts/contacts')
        ->assertForbidden();
});

test('contacts id page renders the contact channel buttons beside their input', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

    $html = $this->actingAs($this->user, 'web')
        ->get('/contacts/contacts/' . $this->contact->id)
        ->assertOk()
        ->getContent();

    $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

    foreach ([
        'address.email_primary' => 'mailto:',
        'address.phone' => 'tel:',
        'address.phone_mobile' => 'tel:',
        'address.url' => 'https://',
    ] as $model => $action) {
        $input = $document->querySelector('input[wire\\:model="' . $model . '"]');
        $prefix = $input->previousElementSibling;

        expect($input->getAttribute('class'))->not->toContain('pl-12')
            ->and($prefix->querySelector('.absolute.inset-y-0'))->toBeNull()
            ->and($prefix->querySelector('button')?->getAttribute('x-on:click.prevent'))->toContain($action);
    }
});
