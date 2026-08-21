<?php

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

test('warehouse bin belongs to a warehouse', function (): void {
    $warehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    expect($bin->warehouse->getKey())->toBe($warehouse->getKey());
});

test('warehouse bin casts its type to an enum', function (): void {
    $warehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Shelf,
    ]);

    expect($bin->fresh()->warehouse_bin_type_enum)->toBe(WarehouseBinTypeEnum::Shelf);
});

test('warehouse bin nests arbitrarily deep', function (): void {
    $warehouse = Warehouse::factory()->create();

    $zone = WarehouseBin::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Zone,
        'is_storage_location' => false,
    ]);
    $rack = WarehouseBin::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'parent_id' => $zone->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Rack,
        'is_storage_location' => false,
    ]);
    $bin = WarehouseBin::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'parent_id' => $rack->getKey(),
    ]);

    expect($bin->ancestorKeys())->toEqualCanonicalizing([$rack->getKey(), $zone->getKey()])
        ->and($zone->descendantKeys())->toEqualCanonicalizing([$rack->getKey(), $bin->getKey()])
        ->and($bin->is_storage_location)->toBeTrue();
});

test('warehouse bin code is unique per warehouse only', function (): void {
    $first = Warehouse::factory()->create();
    $second = Warehouse::factory()->create();

    WarehouseBin::factory()->create(['warehouse_id' => $first->getKey(), 'code' => 'A-01']);
    $other = WarehouseBin::factory()->create(['warehouse_id' => $second->getKey(), 'code' => 'A-01']);

    expect($other->exists)->toBeTrue();
});
