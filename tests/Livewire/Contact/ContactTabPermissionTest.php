<?php

use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Models\Permission;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = ContactModel::factory()->create();

    Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    Permission::findOrCreate('tab.contact.addresses', 'web');
    $this->accountingPermission = Permission::findOrCreate('tab.contact.accounting', 'web');
});

test('a tab without permission does not render its content', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->getKey()])
        ->set('tab', 'contact.accounting')
        ->assertDontSeeLivewire('contact.accounting');
});

test('a tab without permission from the url renders the default tab instead', function (): void {
    $this->user->givePermissionTo('tab.contact.addresses');

    Livewire::withQueryParams(['tab' => 'contact.accounting'])
        ->test(Contact::class, ['id' => $this->contact->getKey()])
        ->assertDontSeeLivewire('contact.accounting')
        ->assertSeeLivewire('contact.addresses')
        ->assertSet('tab', 'contact.addresses');
});

test('a tab with permission renders its content', function (): void {
    $this->user->givePermissionTo($this->accountingPermission);

    Livewire::test(Contact::class, ['id' => $this->contact->getKey()])
        ->set('tab', 'contact.accounting')
        ->assertSeeLivewire('contact.accounting');
});

test('a default tab without permission falls back to the first permitted tab', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->getKey()])
        ->assertDontSeeLivewire('contact.addresses')
        ->assertSeeLivewire('contact.orders')
        ->assertSet('tab', 'contact.orders');
});

test('the default tab renders with permission', function (): void {
    $this->user->givePermissionTo('tab.contact.addresses');

    Livewire::test(Contact::class, ['id' => $this->contact->getKey()])
        ->assertSeeLivewire('contact.addresses')
        ->assertSet('tab', 'contact.addresses');
});

test('an unknown tab does not render a component of that name', function (): void {
    session()->put('flash', ['info' => 'rendered-by-tab-name']);

    Livewire::test(Contact::class, ['id' => $this->contact->getKey()])
        ->set('tab', 'flux::flash')
        ->assertOk()
        ->assertDontSeeHtml('rendered-by-tab-name');
});
