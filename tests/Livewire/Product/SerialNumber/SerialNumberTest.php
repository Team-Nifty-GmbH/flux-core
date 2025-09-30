<?php

use FluxErp\Livewire\Product\SerialNumber\SerialNumber as SerialNumberView;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $serialNumber = SerialNumber::factory()->create();

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'serial_number_id' => $serialNumber->id,
        'posting' => 1,
    ]);

    Livewire::test(SerialNumberView::class, ['id' => $serialNumber->id])
        ->assertOk();
});
