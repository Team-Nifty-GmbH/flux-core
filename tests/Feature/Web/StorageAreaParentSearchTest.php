<?php

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('the parent select can search warehouse bins although the model is not searchable', function (): void {
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'AISLE-42',
        'storage_area_type_enum' => StorageAreaTypeEnum::Aisle,
    ]);

    $this->post(route('search', StorageArea::class), [
        'search' => 'AISLE',
        'searchFields' => ['code', 'name'],
    ])
        ->assertOk()
        ->assertJsonFragment(['id' => $storageArea->getKey()]);
});

test('the search endpoint refuses warehouse bins without search fields', function (): void {
    StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Aisle,
    ]);

    $this->post(route('search', StorageArea::class), ['search' => 'AISLE'])
        ->assertNotFound();
});

test('the search result labels a bin as code and name', function (): void {
    $withName = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'R-01',
        'name' => 'Test Regal',
        'storage_area_type_enum' => StorageAreaTypeEnum::Rack,
    ]);
    $withoutName = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'R-02',
        'name' => null,
        'storage_area_type_enum' => StorageAreaTypeEnum::Rack,
    ]);

    expect($withName->getLabel())->toBe('R-01 - Test Regal')
        ->and($withoutName->getLabel())->toBe('R-02');

    $this->post(route('search', StorageArea::class), [
        'search' => 'R-0',
        'searchFields' => ['code', 'name'],
    ])
        ->assertOk()
        ->assertJsonFragment(['label' => 'R-01 - Test Regal'])
        ->assertJsonFragment(['label' => 'R-02']);
});
