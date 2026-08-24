<?php

use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Livewire\DataTables\WarehouseBinList;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(WarehouseBinList::class)
        ->assertOk();
});

test('lists children below their parent with indentation', function (): void {
    $parent = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Zone,
        'code' => 'ZONE-A',
        'sort_order' => 0,
    ]);

    $child = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'code' => 'ZONE-A-01',
        'sort_order' => 0,
    ]);

    $data = Livewire::test(WarehouseBinList::class)
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
    $parent = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Rack,
    ]);

    WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
    ]);

    $rows = Livewire::test(WarehouseBinList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting()['data'];

    expect($rows[0]['warehouse.name'])->toBe($this->warehouse->name)
        ->and($rows[0]['parent.code'] ?? null)->toBeNull()
        ->and($rows[1]['parent.code'])->toBe($parent->code);
});

test('loads the warehouse name and the parent code for a grandchild row', function (): void {
    $zone = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Zone,
    ]);

    $rack = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $zone->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Rack,
    ]);

    $bin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $rack->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
    ]);

    $data = Livewire::test(WarehouseBinList::class)
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    $rows = $data['data'];

    expect(array_column($rows, 'id'))->toBe([$zone->getKey(), $rack->getKey(), $bin->getKey()])
        ->and($rows[2]['depth'])->toBe(2)
        ->and($rows[2]['parent.code'])->toBe($rack->code)
        ->and($rows[2]['warehouse.name'])->toBe($this->warehouse->name);
});

test('filtering for a nested bin returns its family instead of nothing', function (): void {
    $zone = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Zone,
        'code' => 'ZONE-B',
    ]);
    $nested = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $zone->getKey(),
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin,
        'code' => 'DEEP-BIN',
    ]);

    $rows = Livewire::test(WarehouseBinList::class)
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
