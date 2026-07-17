<?php

use FluxErp\Livewire\Features\CommissionRates;
use FluxErp\Models\CommissionRate;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->id])
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->call('edit')
        ->assertSet('commissionRate.id', null)
        ->assertSet('commissionRate.commission_rate', null)
        ->assertExecutesJs("\$tsui.open.modal('edit-commission-rate-modal');");
});

test('edit with model fills form', function (): void {
    $rate = CommissionRate::factory()->create([
        'user_id' => $this->user->getKey(),
        'commission_rate' => '0.25',
    ]);

    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->call('edit', $rate->getKey())
        ->assertSet('commissionRate.id', $rate->getKey())
        ->assertSet('commissionRate.user_id', $this->user->getKey())
        ->assertExecutesJs("\$tsui.open.modal('edit-commission-rate-modal');");
});

test('can create commission rate', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->call('edit')
        ->set('commissionRate.commission_rate', '15')
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('commission_rates', [
        'user_id' => $this->user->getKey(),
        'commission_rate' => '0.15',
    ]);
});

test('can update commission rate', function (): void {
    $rate = CommissionRate::factory()->create([
        'user_id' => $this->user->getKey(),
        'commission_rate' => '0.10',
    ]);

    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->call('edit', $rate->getKey())
        ->set('commissionRate.commission_rate', '20')
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('commission_rates', [
        'id' => $rate->getKey(),
        'commission_rate' => '0.20',
    ]);
});

test('can delete commission rate', function (): void {
    $rate = CommissionRate::factory()->create([
        'user_id' => $this->user->getKey(),
    ]);

    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->call('delete', $rate->getKey())
        ->assertReturned(true);

    $this->assertSoftDeleted('commission_rates', [
        'id' => $rate->getKey(),
    ]);
});

test('save assigns user_id from component when not set on form', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->call('edit')
        ->set('commissionRate.commission_rate', '10')
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('commission_rates', [
        'user_id' => $this->user->getKey(),
    ]);
});

test('updating category_id clears product_id', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->set('commissionRate.product_id', 123)
        ->set('commissionRate.category_id', 456)
        ->assertSet('commissionRate.product_id', null);
});

test('updating product_id clears category_id', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->getKey()])
        ->set('commissionRate.category_id', 456)
        ->set('commissionRate.product_id', 123)
        ->assertSet('commissionRate.category_id', null);
});
