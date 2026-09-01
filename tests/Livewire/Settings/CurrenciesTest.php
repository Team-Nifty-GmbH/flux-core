<?php

use FluxErp\Livewire\Settings\Currencies;
use FluxErp\Models\Currency;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Currencies::class)
        ->assertOk();
});

test('showEditModal with null resets form and opens modal', function (): void {
    Livewire::test(Currencies::class)
        ->call('showEditModal')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('selectedCurrency.id', null)
        ->assertSet('selectedCurrency.name', null)
        ->assertSet('selectedCurrency.iso', null)
        ->assertSet('selectedCurrency.symbol', null)
        ->assertSet('editModal', true);
});

test('showEditModal with model fills form and opens modal', function (): void {
    $currency = Currency::factory()->create();

    Livewire::test(Currencies::class)
        ->call('showEditModal', $currency->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('selectedCurrency.id', $currency->getKey())
        ->assertSet('selectedCurrency.name', $currency->name)
        ->assertSet('selectedCurrency.iso', $currency->iso)
        ->assertSet('selectedCurrency.symbol', $currency->symbol)
        ->assertSet('editModal', true);
});

test('can create via save', function (): void {
    Livewire::test(Currencies::class)
        ->call('showEditModal')
        ->set('selectedCurrency.name', 'Test Currency')
        ->set('selectedCurrency.iso', 'ZZZ')
        ->set('selectedCurrency.symbol', 'T')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('currencies', [
        'name' => 'Test Currency',
        'iso' => 'ZZZ',
        'symbol' => 'T',
    ]);
});

test('can update via save', function (): void {
    $currency = Currency::factory()->create();

    Livewire::test(Currencies::class)
        ->call('showEditModal', $currency->getKey())
        ->set('selectedCurrency.name', 'Updated Currency')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('currencies', [
        'id' => $currency->getKey(),
        'name' => 'Updated Currency',
    ]);
});

test('can delete', function (): void {
    $currency = Currency::factory()->create();

    Livewire::test(Currencies::class)
        ->call('delete', $currency->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('currencies', ['id' => $currency->getKey()]);
});

test('save validates required fields', function (): void {
    Livewire::test(Currencies::class)
        ->call('showEditModal')
        ->set('selectedCurrency.name', null)
        ->call('save')
        ->assertReturned(false);
});
