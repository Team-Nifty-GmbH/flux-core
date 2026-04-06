<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
});

test('create stock posting', function (): void {
    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ])->validate()->execute();

    expect($posting)->toBeInstanceOf(StockPosting::class);
});

test('create stock posting requires warehouse product and posting', function (): void {
    CreateStockPosting::assertValidationErrors([], ['warehouse_id', 'product_id', 'posting']);
});

test('delete stock posting', function (): void {
    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
    ]);

    expect(DeleteStockPosting::make(['id' => $posting->getKey()])
        ->validate()->execute())->toBeTrue();
});
