<?php

use FluxErp\Livewire\Settings\Countries;
use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Countries::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Countries::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('country.id', null)
        ->assertSet('country.name', null)
        ->assertSet('country.iso_alpha2', null)
        ->assertOpensModal('edit-country-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $country = Country::factory()->create([
        'language_id' => Language::factory(),
        'currency_id' => Currency::factory(),
    ]);

    Livewire::test(Countries::class)
        ->call('edit', $country->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('country.id', $country->getKey())
        ->assertSet('country.name', $country->name)
        ->assertSet('country.iso_alpha2', $country->iso_alpha2)
        ->assertOpensModal('edit-country-modal');
});

test('can create via save', function (): void {
    $language = Language::factory()->create();
    $currency = Currency::factory()->create();

    Livewire::test(Countries::class)
        ->call('edit')
        ->set('country.name', 'Test Country')
        ->set('country.iso_alpha2', 'ZZ')
        ->set('country.language_id', $language->getKey())
        ->set('country.currency_id', $currency->getKey())
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('countries', [
        'name' => 'Test Country',
        'iso_alpha2' => 'ZZ',
    ]);
});

test('can update via save', function (): void {
    $country = Country::factory()->create([
        'language_id' => Language::factory(),
        'currency_id' => Currency::factory(),
    ]);

    Livewire::test(Countries::class)
        ->call('edit', $country->getKey())
        ->set('country.name', 'Updated Country')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('countries', [
        'id' => $country->getKey(),
        'name' => 'Updated Country',
    ]);
});

test('can delete', function (): void {
    $country = Country::factory()->create([
        'language_id' => Language::factory(),
        'currency_id' => Currency::factory(),
    ]);

    Livewire::test(Countries::class)
        ->call('delete', $country->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('countries', ['id' => $country->getKey()]);
});

test('save validates required fields', function (): void {
    Livewire::test(Countries::class)
        ->call('edit')
        ->set('country.name', null)
        ->call('save')
        ->assertReturned(false);
});
