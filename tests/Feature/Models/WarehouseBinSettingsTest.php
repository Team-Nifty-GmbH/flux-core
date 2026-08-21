<?php

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;

test('a warehouse defaults to fifo and to optional bin locations', function (): void {
    $warehouse = Warehouse::factory()->create()->fresh();

    expect($warehouse->requires_bin_location)->toBeFalse()
        ->and($warehouse->stock_removal_strategy_enum)->toBe(StockRemovalStrategyEnum::Fifo);
});

test('a warehouse can require bin locations and use fefo', function (): void {
    $warehouse = Warehouse::factory()->create([
        'requires_bin_location' => true,
        'stock_removal_strategy_enum' => StockRemovalStrategyEnum::Fefo,
    ])->fresh();

    expect($warehouse->requires_bin_location)->toBeTrue()
        ->and($warehouse->stock_removal_strategy_enum)->toBe(StockRemovalStrategyEnum::Fefo);
});

test('a product overrides the warehouse strategy and tracks lots', function (): void {
    $product = Product::factory()->create([
        'is_lot_tracked' => true,
        'stock_removal_strategy_enum' => StockRemovalStrategyEnum::Lifo,
        'min_shelf_life_days' => 30,
    ])->fresh();

    expect($product->is_lot_tracked)->toBeTrue()
        ->and($product->stock_removal_strategy_enum)->toBe(StockRemovalStrategyEnum::Lifo)
        ->and($product->min_shelf_life_days)->toBe(30);
});

test('a product leaves the strategy to its warehouse by default', function (): void {
    $product = Product::factory()->create()->fresh();

    expect($product->stock_removal_strategy_enum)->toBeNull()
        ->and($product->is_lot_tracked)->toBeFalse();
});
