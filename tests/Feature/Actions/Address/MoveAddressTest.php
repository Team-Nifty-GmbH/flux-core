<?php

use FluxErp\Actions\Address\DetachAddress;
use FluxErp\Actions\Address\MoveAddress;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->target = Contact::factory()->create();

    $this->mainAddress = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $this->secondary = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => false,
        'is_invoice_address' => false,
        'is_delivery_address' => false,
    ]);

    $this->contact->update(['main_address_id' => $this->mainAddress->getKey()]);
});

test('move address to another contact', function (): void {
    $address = MoveAddress::make([
        'id' => $this->secondary->getKey(),
        'contact_id' => $this->target->getKey(),
    ])->validate()->execute();

    expect($address->contact_id)->toBe($this->target->getKey());
});

test('moved address becomes the main address of a contact that had none', function (): void {
    MoveAddress::make([
        'id' => $this->secondary->getKey(),
        'contact_id' => $this->target->getKey(),
    ])->validate()->execute();

    expect($this->target->refresh()->main_address_id)->toBe($this->secondary->getKey());
});

test('moving the main address hands the role to a remaining address', function (): void {
    MoveAddress::make([
        'id' => $this->mainAddress->getKey(),
        'contact_id' => $this->target->getKey(),
    ])->validate()->execute();

    expect($this->contact->refresh()->main_address_id)->toBe($this->secondary->getKey())
        ->and($this->secondary->refresh()->is_main_address)->toBeTrue();
});

test('a main address stays the main address at a contact that had none', function (): void {
    MoveAddress::make([
        'id' => $this->mainAddress->getKey(),
        'contact_id' => $this->target->getKey(),
    ])->validate()->execute();

    expect($this->mainAddress->refresh()->is_main_address)->toBeTrue()
        ->and($this->target->refresh()->main_address_id)->toBe($this->mainAddress->getKey());
});

test('move address refuses the contact it already belongs to', function (): void {
    MoveAddress::assertValidationErrors(
        [
            'id' => $this->secondary->getKey(),
            'contact_id' => $this->contact->getKey(),
        ],
        ['contact_id']
    );
});

test('move address requires an existing contact', function (): void {
    MoveAddress::assertValidationErrors(
        [
            'id' => $this->secondary->getKey(),
            'contact_id' => ++$this->target->id,
        ],
        ['contact_id']
    );
});

test('detach address lifts it into a new contact', function (): void {
    $address = DetachAddress::make(['id' => $this->secondary->getKey()])
        ->validate()
        ->execute();

    expect($address->contact_id)->not->toBe($this->contact->getKey())
        ->and($address->is_main_address)->toBeTrue()
        ->and($address->contact->main_address_id)->toBe($address->getKey())
        ->and($address->contact->customer_number)->not->toBeEmpty();
});

test('detach address leaves the old contact intact', function (): void {
    DetachAddress::make(['id' => $this->secondary->getKey()])
        ->validate()
        ->execute();

    expect($this->contact->refresh()->main_address_id)->toBe($this->mainAddress->getKey());
});

test('detach address refuses the only address of a contact', function (): void {
    $lonely = Contact::factory()->create();
    $only = Address::factory()->create([
        'contact_id' => $lonely->getKey(),
        'is_main_address' => true,
    ]);
    $lonely->update(['main_address_id' => $only->getKey()]);

    DetachAddress::assertValidationErrors(['id' => $only->getKey()], ['id']);
});
