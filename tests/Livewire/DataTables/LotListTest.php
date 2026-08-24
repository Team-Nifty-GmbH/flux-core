<?php

use FluxErp\Livewire\DataTables\LotList;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LotList::class)
        ->assertOk();
});

test('lists lots with their product name', function (): void {
    $product = Product::factory()->create();
    $lot = Lot::factory()->create(['product_id' => $product->getKey()]);

    $rows = Livewire::test(LotList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect(array_column($rows, 'id'))->toContain($lot->getKey())
        ->and($rows[0]['product.name'])->toBe($product->name);
});
