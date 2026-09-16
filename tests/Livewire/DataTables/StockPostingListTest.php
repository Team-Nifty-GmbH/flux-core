<?php

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Livewire\DataTables\StockPostingList;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(StockPostingList::class)
        ->assertOk();
});

test('shows bin and lot columns', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
        'is_storage_location' => true,
    ]);
    $lot = Lot::factory()->create(['product_id' => $product->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
    ]);

    $rows = Livewire::test(StockPostingList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect($rows[0]['storage_area.code'])->toBe($storageArea->code)
        ->and($rows[0]['lot.lot_number'])->toBe($lot->lot_number);
});
