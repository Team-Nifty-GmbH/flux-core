<?php

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Livewire\Settings\Warehouses;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Warehouses::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Warehouses::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('warehouse.id', null)
        ->assertSet('warehouse.name', null)
        ->assertSet('warehouse.is_default', false)
        ->assertOpensModal('edit-warehouse-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $warehouse = Warehouse::factory()->create();

    Livewire::test(Warehouses::class)
        ->call('edit', $warehouse->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('warehouse.id', $warehouse->getKey())
        ->assertSet('warehouse.name', $warehouse->name)
        ->assertOpensModal('edit-warehouse-modal');
});

test('can create warehouse', function (): void {
    Livewire::test(Warehouses::class)
        ->assertOk()
        ->call('edit')
        ->set('warehouse.name', $name = Str::uuid()->toString())
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('warehouses', [
        'name' => $name,
    ]);
});

test('can update warehouse', function (): void {
    $warehouse = Warehouse::factory()->create();

    Livewire::test(Warehouses::class)
        ->assertOk()
        ->call('edit', $warehouse->getKey())
        ->assertSet('warehouse.id', $warehouse->getKey())
        ->set('warehouse.name', 'Updated Warehouse Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($warehouse->refresh()->name)->toEqual('Updated Warehouse Name');
});

test('can delete warehouse', function (): void {
    $warehouse = Warehouse::factory()->create();

    Livewire::test(Warehouses::class)
        ->assertOk()
        ->call('edit', $warehouse->getKey())
        ->assertSet('warehouse.id', $warehouse->getKey())
        ->call('delete')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('warehouses', [
        'id' => $warehouse->getKey(),
    ]);
});

test('can set bin location requirement and removal strategy', function (): void {
    $warehouse = Warehouse::factory()->create();

    Livewire::test(Warehouses::class)
        ->call('edit', $warehouse->getKey())
        ->set('warehouse.requires_bin_location', true)
        ->set('warehouse.stock_removal_strategy_enum', StockRemovalStrategyEnum::Fefo->value)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $warehouse->refresh();

    expect($warehouse->requires_bin_location)->toBeTrue();
    expect($warehouse->stock_removal_strategy_enum)->toBe(StockRemovalStrategyEnum::Fefo);
});
