<?php

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Enums\WarehouseBinTypeEnum;

test('warehouse bin type enum exposes all values', function (): void {
    expect(WarehouseBinTypeEnum::values())
        ->toBe(['aisle', 'bin', 'goods-in', 'goods-out', 'packing', 'quarantine', 'rack', 'shelf', 'zone']);
});

test('stock removal strategy enum exposes all values', function (): void {
    expect(StockRemovalStrategyEnum::values())
        ->toBe(['fefo', 'fifo', 'lifo']);
});

test('stock removal strategy enum is localizable', function (): void {
    expect(StockRemovalStrategyEnum::toArray())
        ->toHaveKeys(['fefo', 'fifo', 'lifo']);
});
