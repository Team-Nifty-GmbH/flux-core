<?php

use FluxErp\Livewire\Product\StockPostingList;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
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
