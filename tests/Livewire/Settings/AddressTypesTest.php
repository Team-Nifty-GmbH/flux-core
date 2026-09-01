<?php

use FluxErp\Livewire\Settings\AddressTypes;
use FluxErp\Models\AddressType;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressTypes::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(AddressTypes::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('addressType.id', null)
        ->assertSet('addressType.name', null)
        ->assertOpensModal('edit-address-type-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $addressType = AddressType::factory()->create();

    Livewire::test(AddressTypes::class)
        ->call('edit', $addressType->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('addressType.id', $addressType->getKey())
        ->assertSet('addressType.name', $addressType->name)
        ->assertOpensModal('edit-address-type-modal');
});

test('can create via save', function (): void {
    Livewire::test(AddressTypes::class)
        ->call('edit')
        ->set('addressType.name', 'Test Address Type')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('address_types', ['name' => 'Test Address Type']);
});

test('can update via save', function (): void {
    $addressType = AddressType::factory()->create();

    Livewire::test(AddressTypes::class)
        ->call('edit', $addressType->getKey())
        ->set('addressType.name', 'Updated Address Type')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('address_types', [
        'id' => $addressType->getKey(),
        'name' => 'Updated Address Type',
    ]);
});

test('can delete', function (): void {
    $addressType = AddressType::factory()->create();

    Livewire::test(AddressTypes::class)
        ->call('delete', $addressType->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('address_types', ['id' => $addressType->getKey()]);
});

test('save validates required fields', function (): void {
    Livewire::test(AddressTypes::class)
        ->call('edit')
        ->set('addressType.name', null)
        ->call('save')
        ->assertReturned(false);
});
