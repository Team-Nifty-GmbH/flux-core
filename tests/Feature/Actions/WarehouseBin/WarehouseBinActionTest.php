<?php

use FluxErp\Actions\WarehouseBin\CreateWarehouseBin;
use FluxErp\Actions\WarehouseBin\DeleteWarehouseBin;
use FluxErp\Actions\WarehouseBin\UpdateWarehouseBin;
use FluxErp\Enums\WarehouseBinTypeEnum;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('create warehouse bin', function (): void {
    $bin = CreateWarehouseBin::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01-03-B',
        'name' => 'Fach B',
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin->value,
        'is_storage_location' => true,
    ])->validate()->execute();

    expect($bin)->toBeInstanceOf(WarehouseBin::class)
        ->code->toBe('A-01-03-B')
        ->and($bin->warehouse_bin_type_enum)->toBe(WarehouseBinTypeEnum::Bin);
});

test('create warehouse bin requires warehouse and code', function (): void {
    CreateWarehouseBin::assertValidationErrors([], 'warehouse_id');
    CreateWarehouseBin::assertValidationErrors(['warehouse_id' => $this->warehouse->getKey()], 'code');
});

test('create warehouse bin rejects a duplicate code in the same warehouse', function (): void {
    WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01']);

    CreateWarehouseBin::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01',
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin->value,
    ], 'code');
});

test('create warehouse bin rejects a parent from another warehouse', function (): void {
    $other = Warehouse::factory()->create();
    $foreignParent = WarehouseBin::factory()->create(['warehouse_id' => $other->getKey()]);

    CreateWarehouseBin::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $foreignParent->getKey(),
        'code' => 'A-02',
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin->value,
    ], 'parent_id');
});

test('update warehouse bin', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    $updated = UpdateWarehouseBin::make([
        'id' => $bin->getKey(),
        'name' => 'Umbenannt',
        'is_active' => false,
    ])->validate()->execute();

    expect($updated->name)->toBe('Umbenannt')
        ->and($updated->is_active)->toBeFalse();
});

test('update warehouse bin rejects a duplicate code in the same warehouse', function (): void {
    WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01']);
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-02']);

    UpdateWarehouseBin::assertValidationErrors(['id' => $bin->getKey(), 'code' => 'A-01'], 'code');
});

test('update warehouse bin rejects a parent from another warehouse', function (): void {
    $other = Warehouse::factory()->create();
    $foreignParent = WarehouseBin::factory()->create(['warehouse_id' => $other->getKey()]);
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    UpdateWarehouseBin::assertValidationErrors([
        'id' => $bin->getKey(),
        'parent_id' => $foreignParent->getKey(),
    ], 'parent_id');
});

test('update warehouse bin rejects moving to another warehouse while its parent stays behind', function (): void {
    $parent = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $bin = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $other = Warehouse::factory()->create();

    UpdateWarehouseBin::assertValidationErrors([
        'id' => $bin->getKey(),
        'warehouse_id' => $other->getKey(),
    ], 'parent_id');
});

test('create warehouse bin rejects a code held by a trashed bin', function (): void {
    WarehouseBin::factory()
        ->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01'])
        ->delete();

    CreateWarehouseBin::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01',
        'warehouse_bin_type_enum' => WarehouseBinTypeEnum::Bin->value,
    ], 'code');
});

test('update warehouse bin rejects a parent from its own subtree', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $child = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $bin->getKey(),
    ]);
    $grandChild = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $child->getKey(),
    ]);

    UpdateWarehouseBin::assertValidationErrors([
        'id' => $bin->getKey(),
        'parent_id' => $grandChild->getKey(),
    ], 'parent_id');
});

test('update warehouse bin rejects itself as its own parent', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    UpdateWarehouseBin::assertValidationErrors([
        'id' => $bin->getKey(),
        'parent_id' => $bin->getKey(),
    ], 'parent_id');
});

test('update warehouse bin rejects moving to another warehouse while its children stay behind', function (): void {
    $parent = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $other = Warehouse::factory()->create();

    UpdateWarehouseBin::assertValidationErrors([
        'id' => $parent->getKey(),
        'warehouse_id' => $other->getKey(),
    ], 'warehouse_id');
});

test('delete warehouse bin', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    expect(DeleteWarehouseBin::make(['id' => $bin->getKey()])->validate()->execute())->toBeTrue();
});

test('delete warehouse bin refuses while stock remains', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $product = Product::factory()->create();

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'posting' => 10,
    ]);

    DeleteWarehouseBin::assertValidationErrors(['id' => $bin->getKey()], 'stock_postings');
});

test('delete warehouse bin refuses while stock postings reference it after netting to zero', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $product = Product::factory()->create();

    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'posting' => 10,
    ]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ]);

    DeleteWarehouseBin::assertValidationErrors(['id' => $bin->getKey()], 'stock_postings');
});

test('delete warehouse bin refuses while it has child bins', function (): void {
    $parent = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);

    DeleteWarehouseBin::assertValidationErrors(['id' => $parent->getKey()], 'id');
});
