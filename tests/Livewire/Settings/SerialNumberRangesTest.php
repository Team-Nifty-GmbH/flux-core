<?php

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Livewire\Settings\SerialNumberRanges;
use FluxErp\Models\Order;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberRanges::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(SerialNumberRanges::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('serialNumberRange.id', null)
        ->assertSet('serialNumberRange.model_type', null)
        ->assertSet('serialNumberRange.type', null)
        ->assertSet('serialNumberRange.prefix', null)
        ->assertOpensModal('edit-serial-number-range-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $serialNumberRange = CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(Order::class),
        'type' => 'test_number',
        'prefix' => 'TST-',
        'start_number' => 1,
    ])->validate()->execute();

    Livewire::test(SerialNumberRanges::class)
        ->call('edit', $serialNumberRange->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('serialNumberRange.id', $serialNumberRange->getKey())
        ->assertSet('serialNumberRange.prefix', 'TST-')
        ->assertOpensModal('edit-serial-number-range-modal');
});

test('can create serial number range', function (): void {
    Livewire::test(SerialNumberRanges::class)
        ->assertOk()
        ->call('edit')
        ->set('serialNumberRange.tenant_id', $this->dbTenant->getKey())
        ->set('serialNumberRange.model_type', morph_alias(Order::class))
        ->set('serialNumberRange.type', 'livewire_test')
        ->set('serialNumberRange.prefix', 'LW-')
        ->set('serialNumberRange.current_number', 0)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('serial_number_ranges', [
        'type' => 'livewire_test',
        'prefix' => 'LW-',
    ]);
});

test('can update serial number range', function (): void {
    $serialNumberRange = CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(Order::class),
        'type' => 'update_test',
        'prefix' => 'OLD-',
        'start_number' => 100,
    ])->validate()->execute();

    Livewire::test(SerialNumberRanges::class)
        ->assertOk()
        ->call('edit', $serialNumberRange->getKey())
        ->assertSet('serialNumberRange.id', $serialNumberRange->getKey())
        ->set('serialNumberRange.prefix', 'NEW-')
        ->set('serialNumberRange.length', 4)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($serialNumberRange->refresh()->prefix)->toEqual('NEW-');
});

test('can delete serial number range', function (): void {
    $serialNumberRange = CreateSerialNumberRange::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'model_type' => morph_alias(Order::class),
        'type' => 'delete_test',
        'start_number' => 1,
    ])->validate()->execute();

    Livewire::test(SerialNumberRanges::class)
        ->assertOk()
        ->call('delete', $serialNumberRange->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('serial_number_ranges', [
        'id' => $serialNumberRange->getKey(),
        'deleted_at' => null,
    ]);
});
