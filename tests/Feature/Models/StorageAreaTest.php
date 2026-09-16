<?php

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Models\Warehouse;
use FluxErp\Models\StorageArea;

test('storage area belongs to a warehouse', function (): void {
    $warehouse = Warehouse::factory()->create();
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    expect($storageArea->warehouse->getKey())->toBe($warehouse->getKey());
});

test('storage area casts its type to an enum', function (): void {
    $warehouse = Warehouse::factory()->create();
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Shelf,
    ]);

    expect($storageArea->fresh()->storage_area_type_enum)->toBe(StorageAreaTypeEnum::Shelf);
});

test('storage area nests arbitrarily deep', function (): void {
    $warehouse = Warehouse::factory()->create();

    $zone = StorageArea::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Zone,
        'is_storage_location' => false,
    ]);
    $rack = StorageArea::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'parent_id' => $zone->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Rack,
        'is_storage_location' => false,
    ]);
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'parent_id' => $rack->getKey(),
    ]);

    expect($storageArea->ancestorKeys())->toEqualCanonicalizing([$rack->getKey(), $zone->getKey()])
        ->and($zone->descendantKeys())->toEqualCanonicalizing([$rack->getKey(), $storageArea->getKey()])
        ->and($storageArea->is_storage_location)->toBeTrue();
});

test('storage area code is unique per warehouse only', function (): void {
    $first = Warehouse::factory()->create();
    $second = Warehouse::factory()->create();

    StorageArea::factory()->create(['warehouse_id' => $first->getKey(), 'code' => 'A-01']);
    $other = StorageArea::factory()->create(['warehouse_id' => $second->getKey(), 'code' => 'A-01']);

    expect($other->exists)->toBeTrue();
});

test('storage areas receive their walking order on creation', function (): void {
    $warehouse = Warehouse::factory()->create();

    $first = StorageArea::factory()->create(['warehouse_id' => $warehouse->getKey()]);
    $second = StorageArea::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    expect($second->sort_number)->toBeGreaterThan($first->sort_number);
});
