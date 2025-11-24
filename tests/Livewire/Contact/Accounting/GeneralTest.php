<?php

use FluxErp\Livewire\Contact\Accounting\General;
use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Models\PaymentType;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = ContactModel::factory()->create([
        'client_id' => $this->dbClient->id,
        'payment_type_id' => PaymentType::default()->id,
        'discount_percent' => 0.05,
    ]);

    Address::factory()->create([
        'client_id' => $this->dbClient->id,
        'contact_id' => $this->contact->id,
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(General::class)
        ->assertOk();
});

test('discount_percent is converted from decimal to percent for UI', function (): void {
    $component = Livewire::test(Contact::class, ['id' => $this->contact->id]);

    expect($component->get('contact.discount_percent'))->toBe(5.0);
});

test('discount_percent is converted from percent to decimal on save', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])
        ->set('edit', true)
        ->set('contact.discount_percent', 10)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('contact.discount_percent', 10);

    expect($this->contact->refresh()->discount_percent)->toBe(0.1);
});
