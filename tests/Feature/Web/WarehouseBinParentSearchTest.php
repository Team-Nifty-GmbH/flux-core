<?php

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('the parent select can search warehouse bins although the model is not searchable', function (): void {
    $bin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'AISLE-42',
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Aisle,
    ]);

    $this->post(route('search', WarehouseBin::class), [
        'search' => 'AISLE',
        'searchFields' => ['code', 'name'],
    ])
        ->assertOk()
        ->assertJsonFragment(['id' => $bin->getKey()]);
});

test('the search endpoint refuses warehouse bins without search fields', function (): void {
    WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Aisle,
    ]);

    $this->post(route('search', WarehouseBin::class), ['search' => 'AISLE'])
        ->assertNotFound();
});

test('the search result labels a bin as code and name', function (): void {
    $withName = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'R-01',
        'name' => 'Test Regal',
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Rack,
    ]);
    $withoutName = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'R-02',
        'name' => null,
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Rack,
    ]);

    expect($withName->getLabel())->toBe('R-01 - Test Regal')
        ->and($withoutName->getLabel())->toBe('R-02');

    $this->post(route('search', WarehouseBin::class), [
        'search' => 'R-0',
        'searchFields' => ['code', 'name'],
    ])
        ->assertOk()
        ->assertJsonFragment(['label' => 'R-01 - Test Regal'])
        ->assertJsonFragment(['label' => 'R-02']);
});
