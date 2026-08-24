<?php

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Livewire\Settings\WarehouseBins;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(WarehouseBins::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(WarehouseBins::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('warehouseBin.id', null)
        ->assertSet('warehouseBin.code', null)
        ->assertSet('warehouseBin.parent_id', null)
        ->assertSet('warehouseBin.is_active', true)
        ->assertSet('warehouseBin.is_storage_location', false)
        ->assertOpensModal('edit-warehouse-bin-modal');
});

test('edit with model fills form', function (): void {
    $warehouseBin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
    ]);

    Livewire::test(WarehouseBins::class)
        ->call('edit', $warehouseBin->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('warehouseBin.id', $warehouseBin->getKey())
        ->assertSet('warehouseBin.code', $warehouseBin->code)
        ->assertSet('warehouseBin.warehouse_id', $this->warehouse->getKey());
});

test('can create warehouse bin', function (): void {
    $code = Str::uuid()->toString();

    Livewire::test(WarehouseBins::class)
        ->call('edit')
        ->set('warehouseBin.warehouse_id', $this->warehouse->getKey())
        ->set('warehouseBin.code', $code)
        ->set('warehouseBin.warehouse_bin_type_enum', WarehouseBinTypeEnum::Bin->value)
        ->set('warehouseBin.is_storage_location', true)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('warehouse_bins', [
        'code' => $code,
        'warehouse_id' => $this->warehouse->getKey(),
        'is_storage_location' => true,
    ]);
});

test('can update warehouse bin', function (): void {
    $warehouseBin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
    ]);

    Livewire::test(WarehouseBins::class)
        ->call('edit', $warehouseBin->getKey())
        ->set('warehouseBin.name', 'Updated Bin Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($warehouseBin->refresh()->name)->toEqual('Updated Bin Name');
});

test('can delete warehouse bin', function (): void {
    $warehouseBin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
    ]);

    Livewire::test(WarehouseBins::class)
        ->call('delete', $warehouseBin->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('warehouse_bins', ['id' => $warehouseBin->getKey()]);
});

test('save fails without required fields', function (): void {
    Livewire::test(WarehouseBins::class)
        ->call('edit')
        ->set('warehouseBin.code', null)
        ->call('save')
        ->assertOk()
        ->assertHasErrors(['warehouseBin.code'])
        ->assertReturned(false);
});

test('the parent select sends search fields so the search endpoint accepts it', function (): void {
    $html = Livewire::test(WarehouseBins::class)
        ->assertOk()
        ->html();

    expect($html)->toContain('warehouse-bin-parent-id')
        ->and(html_entity_decode($html))->toContain('searchFields');
});
