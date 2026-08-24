<?php

use FluxErp\Livewire\Product\ExpiringStockList;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();

    $this->layerExpiringIn = function (int $days): StockPosting {
        $lot = Lot::factory()->create([
            'product_id' => $this->product->getKey(),
            'expires_at' => now()->addDays($days)->toDateString(),
        ]);

        return StockPosting::factory()->create([
            'warehouse_id' => $this->warehouse->getKey(),
            'product_id' => $this->product->getKey(),
            'lot_id' => $lot->getKey(),
            'posting' => 10,
            'remaining_stock' => 10,
        ]);
    };
});

test('renders successfully', function (): void {
    Livewire::test(ExpiringStockList::class)
        ->assertOk();
});

test('lists only layers expiring inside the default window', function (): void {
    $soon = ($this->layerExpiringIn)(10);
    ($this->layerExpiringIn)(90);

    $rows = Livewire::test(ExpiringStockList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect(array_column($rows, 'id'))->toBe([$soon->getKey()]);
});

test('widening the window brings in later layers', function (): void {
    $soon = ($this->layerExpiringIn)(10);
    $later = ($this->layerExpiringIn)(90);

    $rows = Livewire::test(ExpiringStockList::class)
        ->set('days', 120)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect(array_column($rows, 'id'))
        ->toContain($soon->getKey())
        ->toContain($later->getKey());
});
