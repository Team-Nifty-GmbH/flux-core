<?php

use FluxErp\Livewire\Settings\VatRates;
use FluxErp\Models\VatRate;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VatRates::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(VatRates::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('vatRate.id', null)
        ->assertSet('vatRate.name', null)
        ->assertSet('vatRate.rate_percentage', null)
        ->assertOpensModal('edit-vat-rate-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $vatRate = VatRate::factory()->create();

    Livewire::test(VatRates::class)
        ->call('edit', $vatRate->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('vatRate.id', $vatRate->getKey())
        ->assertSet('vatRate.name', $vatRate->name)
        ->assertSet('vatRate.rate_percentage', $vatRate->rate_percentage)
        ->assertOpensModal('edit-vat-rate-modal');
});

test('can create vat rate', function (): void {
    Livewire::test(VatRates::class)
        ->assertOk()
        ->call('edit')
        ->set('vatRate.name', $name = Str::uuid()->toString())
        ->set('vatRate.rate_percentage_frontend', 19)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('vat_rates', [
        'name' => $name,
        'rate_percentage' => 0.19,
    ]);
});

test('can update vat rate', function (): void {
    $vatRate = VatRate::factory()->create();

    Livewire::test(VatRates::class)
        ->assertOk()
        ->call('edit', $vatRate->getKey())
        ->assertSet('vatRate.id', $vatRate->getKey())
        ->set('vatRate.name', 'Updated VatRate Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($vatRate->refresh()->name)->toEqual('Updated VatRate Name');
});

test('can delete vat rate', function (): void {
    $vatRate = VatRate::factory()->create(['is_default' => false]);

    Livewire::test(VatRates::class)
        ->assertOk()
        ->call('delete', $vatRate->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('vat_rates', [
        'id' => $vatRate->getKey(),
    ]);
});
