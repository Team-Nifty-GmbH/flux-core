<?php

use FluxErp\Livewire\Settings\PaymentTypes;
use FluxErp\Models\PaymentType;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentTypes::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(PaymentTypes::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('paymentType.id', null)
        ->assertSet('paymentType.name', null)
        ->assertSet('paymentType.is_active', true)
        ->assertSet('paymentType.is_sales', true)
        ->assertOpensModal('edit-payment-type-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    Livewire::test(PaymentTypes::class)
        ->call('edit', $paymentType->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('paymentType.id', $paymentType->getKey())
        ->assertSet('paymentType.name', $paymentType->name)
        ->assertOpensModal('edit-payment-type-modal');
});

test('can create payment type', function (): void {
    Livewire::test(PaymentTypes::class)
        ->assertOk()
        ->call('edit')
        ->set('paymentType.name', $name = Str::uuid()->toString())
        ->set('paymentType.tenants', [$this->dbTenant->getKey()])
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('payment_types', [
        'name' => $name,
    ]);
});

test('can update payment type', function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    Livewire::test(PaymentTypes::class)
        ->assertOk()
        ->call('edit', $paymentType->getKey())
        ->assertSet('paymentType.id', $paymentType->getKey())
        ->set('paymentType.name', 'Updated PaymentType Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($paymentType->refresh()->name)->toEqual('Updated PaymentType Name');
});

test('can delete payment type', function (): void {
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create(['is_default' => false]);

    Livewire::test(PaymentTypes::class)
        ->assertOk()
        ->call('delete', $paymentType->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('payment_types', [
        'id' => $paymentType->getKey(),
    ]);
});
