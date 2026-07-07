<?php

use FluxErp\Livewire\Product\SerialNumber\SerialNumberList;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberList::class)
        ->assertOk();
});

test('edit resets form and opens modal', function (): void {
    Livewire::test(SerialNumberList::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('stockPosting.id', null)
        ->assertSet('stockPosting.product_id', null)
        ->assertSet('stockPosting.address', ['id' => null, 'quantity' => 1])
        ->assertOpensModal('create-serial-number-modal');
});

test('can save serial number', function (): void {
    $warehouse = Warehouse::factory()->create(['is_default' => true]);
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    Livewire::test(SerialNumberList::class)
        ->call('edit')
        ->set('stockPosting.product_id', $product->getKey())
        ->set('stockPosting.serial_number.serial_number', 'SN-12345')
        ->set('stockPosting.purchase_price', 99.99)
        ->set('stockPosting.address', [])
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('serial_numbers', [
        'serial_number' => 'SN-12345',
    ]);

    $this->assertDatabaseHas('stock_postings', [
        'product_id' => $product->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'posting' => 0,
    ]);
});

test('save validation fails with missing product', function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    Livewire::test(SerialNumberList::class)
        ->call('edit')
        ->set('stockPosting.product_id', null)
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});
