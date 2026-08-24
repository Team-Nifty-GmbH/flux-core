<?php

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Livewire\Product\StockPostingList;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create(['is_default' => true]);

    $this->product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
});

test('renders successfully', function (): void {
    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->assertOk();
});

test('create resets form and opens modal', function (): void {
    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('create')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('stockPosting.id', null)
        ->assertSet('stockPosting.warehouse_id', $this->warehouse->getKey())
        ->assertOpensModal('create-stock-posting-modal');
});

test('create uses warehouse id if set', function (): void {
    $otherWarehouse = Warehouse::factory()->create();

    Livewire::test(StockPostingList::class, [
        'productId' => $this->product->getKey(),
        'warehouseId' => $otherWarehouse->getKey(),
    ])
        ->call('create')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('stockPosting.warehouse_id', $otherWarehouse->getKey());
});

test('can save stock posting', function (): void {
    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('create')
        ->set('stockPosting.posting', 10)
        ->set('stockPosting.purchase_price', 25.50)
        ->set('stockPosting.description', 'Test posting')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('stock_postings', [
        'product_id' => $this->product->getKey(),
        'warehouse_id' => $this->warehouse->getKey(),
        'posting' => 10,
        'description' => 'Test posting',
    ]);
});

test('save validation fails with missing posting', function (): void {
    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('create')
        ->set('stockPosting.posting', null)
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});

test('mount detects has serial numbers', function (): void {
    $productWithSerials = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['has_serial_numbers' => true]);

    $component = Livewire::test(StockPostingList::class, [
        'productId' => $productWithSerials->getKey(),
    ]);

    expect($component->get('hasSerialNumbers'))->toBeTrue();
});

test('mount detects no serial numbers', function (): void {
    $component = Livewire::test(StockPostingList::class, [
        'productId' => $this->product->getKey(),
    ]);

    expect($component->get('hasSerialNumbers'))->toBeFalse();
});

test('updated warehouse id sets user filters', function (): void {
    $otherWarehouse = Warehouse::factory()->create();

    $component = Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->set('warehouseId', $otherWarehouse->getKey());

    $filters = $component->get('userFilters');
    expect($filters)->not->toBeEmpty();
    expect($filters[0][0]['column'])->toEqual('warehouse_id');
    expect($filters[0][0]['value'])->toEqual($otherWarehouse->getKey());
});

test('can save stock posting with bin and lot', function (): void {
    $warehouseBin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'is_storage_location' => true,
    ]);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('create')
        ->set('stockPosting.posting', 7)
        ->set('stockPosting.warehouse_bin_id', $warehouseBin->getKey())
        ->set('stockPosting.lot_id', $lot->getKey())
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('stock_postings', [
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $warehouseBin->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 7,
    ]);
});

test('view data lists only the lots of the product', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    Lot::factory()->create(['product_id' => Product::factory()]);

    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->assertOk()
        ->assertViewHas('lots', fn (array $lots): bool => array_column($lots, 'id') === [$lot->getKey()]);
});

test('transfer resets form and opens modal', function (): void {
    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('transfer')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('stockTransfer.product_id', $this->product->getKey())
        ->assertSet('stockTransfer.warehouse_id', $this->warehouse->getKey())
        ->assertSet('stockTransfer.from_warehouse_bin_id', null)
        ->assertOpensModal('transfer-stock-modal');
});

test('can transfer stock between bins', function (): void {
    $source = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'is_storage_location' => true,
    ]);
    $target = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'is_storage_location' => true,
    ]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $source->getKey(),
        'posting' => 10,
        'remaining_stock' => 10,
    ]);

    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('transfer')
        ->set('stockTransfer.from_warehouse_bin_id', $source->getKey())
        ->set('stockTransfer.to_warehouse_bin_id', $target->getKey())
        ->set('stockTransfer.amount', '4')
        ->call('saveTransfer')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('stock_postings', [
        'warehouse_bin_id' => $target->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 4,
    ]);
    $this->assertDatabaseHas('stock_postings', [
        'warehouse_bin_id' => $source->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => -4,
    ]);
});

test('transfer fails when the source bin holds too little stock', function (): void {
    $source = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'is_storage_location' => true,
    ]);
    $target = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'is_storage_location' => true,
    ]);

    Livewire::test(StockPostingList::class, ['productId' => $this->product->getKey()])
        ->call('transfer')
        ->set('stockTransfer.from_warehouse_bin_id', $source->getKey())
        ->set('stockTransfer.to_warehouse_bin_id', $target->getKey())
        ->set('stockTransfer.amount', '5')
        ->call('saveTransfer')
        ->assertOk()
        ->assertReturned(false);

    $this->assertDatabaseMissing('stock_postings', [
        'warehouse_bin_id' => $target->getKey(),
    ]);
});
