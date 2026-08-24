<?php

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Livewire\DataTables\StockPostingList;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(StockPostingList::class)
        ->assertOk();
});

test('shows bin and lot columns', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $warehouseBin = WarehouseBin::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'is_storage_location' => true,
    ]);
    $lot = Lot::factory()->create(['product_id' => $product->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'warehouse_bin_id' => $warehouseBin->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
    ]);

    $rows = Livewire::test(StockPostingList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect($rows[0]['warehouse_bin.code'])->toBe($warehouseBin->code)
        ->and($rows[0]['lot.lot_number'])->toBe($lot->lot_number);
});
