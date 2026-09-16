<?php

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Livewire\DataTables\StorageAreaList;
use FluxErp\Models\Warehouse;
use FluxErp\Models\StorageArea;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(StorageAreaList::class)
        ->assertOk();
});

test('lists children below their parent with indentation', function (): void {
    $parent = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Zone,
        'code' => 'ZONE-A',
        'sort_number' => 0,
    ]);

    $child = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
        'code' => 'ZONE-A-01',
        'sort_number' => 0,
    ]);

    $data = Livewire::test(StorageAreaList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    $rows = $data['data'];

    expect(array_column($rows, 'id'))->toBe([$parent->getKey(), $child->getKey()])
        ->and($rows[0]['depth'])->toBe(0)
        ->and($rows[1]['depth'])->toBe(1)
        ->and($rows[1]['indentation'])->toContain('min-width:20px');
});

test('loads the warehouse name and the parent code for every row', function (): void {
    $parent = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Rack,
    ]);

    StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ]);

    $rows = Livewire::test(StorageAreaList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect($rows[0]['warehouse.name'])->toBe($this->warehouse->name)
        ->and($rows[0]['parent.code'] ?? null)->toBeNull()
        ->and($rows[1]['parent.code'])->toBe($parent->code);
});

test('loads the warehouse name and the parent code for a grandchild row', function (): void {
    $zone = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Zone,
    ]);

    $rack = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $zone->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Rack,
    ]);

    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $rack->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ]);

    $data = Livewire::test(StorageAreaList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    $rows = $data['data'];

    expect(array_column($rows, 'id'))->toBe([$zone->getKey(), $rack->getKey(), $storageArea->getKey()])
        ->and($rows[2]['depth'])->toBe(2)
        ->and($rows[2]['parent.code'])->toBe($rack->code)
        ->and($rows[2]['warehouse.name'])->toBe($this->warehouse->name);
});

test('filtering for a nested bin returns its family instead of nothing', function (): void {
    $zone = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Zone,
        'code' => 'ZONE-B',
    ]);
    $nested = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $zone->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
        'code' => 'DEEP-BIN',
    ]);

    $rows = Livewire::test(StorageAreaList::class)
        ->set('userFilters', [[[
            'column' => 'code',
            'operator' => '=',
            'value' => 'DEEP-BIN',
        ]]])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect(array_column($rows, 'id'))->toBe([$zone->getKey(), $nested->getKey()]);
});
