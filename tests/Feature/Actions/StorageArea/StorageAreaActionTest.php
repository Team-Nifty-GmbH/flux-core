<?php

use FluxErp\Actions\StorageArea\CreateStorageArea;
use FluxErp\Actions\StorageArea\DeleteStorageArea;
use FluxErp\Actions\StorageArea\UpdateStorageArea;
use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('create storage area', function (): void {
    $storageArea = CreateStorageArea::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01-03-B',
        'name' => 'Container B',
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
        'is_storage_location' => true,
    ])->validate()->execute();

    expect($storageArea)->toBeInstanceOf(StorageArea::class)
        ->code->toBe('A-01-03-B')
        ->and($storageArea->storage_area_type_enum->value)->toBe(StorageAreaTypeEnum::Container);

    $this->assertDatabaseHas('storage_areas', [
        'id' => $storageArea->getKey(),
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01-03-B',
        'name' => 'Container B',
        'is_storage_location' => true,
    ]);
});

test('create storage area requires warehouse and code', function (): void {
    CreateStorageArea::assertValidationErrors([], 'warehouse_id');
    CreateStorageArea::assertValidationErrors(['warehouse_id' => $this->warehouse->getKey()], 'code');
});

test('create storage area rejects a duplicate code in the same warehouse', function (): void {
    StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01']);

    CreateStorageArea::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01',
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ], 'code');
});

test('create storage area rejects a parent from another warehouse', function (): void {
    $other = Warehouse::factory()->create();
    $foreignParent = StorageArea::factory()->create(['warehouse_id' => $other->getKey()]);

    CreateStorageArea::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $foreignParent->getKey(),
        'code' => 'A-02',
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ], 'parent_id');
});

test('update storage area', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    $updated = UpdateStorageArea::make([
        'id' => $storageArea->getKey(),
        'name' => 'Umbenannt',
        'is_active' => false,
    ])->validate()->execute();

    expect($updated->name)->toBe('Umbenannt')
        ->and($updated->is_active)->toBeFalse();

    $this->assertDatabaseHas('storage_areas', [
        'id' => $storageArea->getKey(),
        'name' => 'Umbenannt',
        'is_active' => false,
    ]);
});

test('update storage area rejects a duplicate code in the same warehouse', function (): void {
    StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01']);
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-02']);

    UpdateStorageArea::assertValidationErrors(['id' => $storageArea->getKey(), 'code' => 'A-01'], 'code');
});

test('update storage area rejects a parent from another warehouse', function (): void {
    $other = Warehouse::factory()->create();
    $foreignParent = StorageArea::factory()->create(['warehouse_id' => $other->getKey()]);
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    UpdateStorageArea::assertValidationErrors([
        'id' => $storageArea->getKey(),
        'parent_id' => $foreignParent->getKey(),
    ], 'parent_id');
});

test('update storage area rejects moving to another warehouse while its parent stays behind', function (): void {
    $parent = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $other = Warehouse::factory()->create();

    UpdateStorageArea::assertValidationErrors([
        'id' => $storageArea->getKey(),
        'warehouse_id' => $other->getKey(),
    ], 'parent_id');
});

test('create storage area reuses a code released by a trashed storage area', function (): void {
    StorageArea::factory()
        ->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01'])
        ->delete();

    $storageArea = CreateStorageArea::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01',
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ])
        ->validate()
        ->execute();

    expect($storageArea->code)->toBe('A-01');

    $this->assertDatabaseHas('storage_areas', [
        'id' => $storageArea->getKey(),
        'warehouse_id' => $this->warehouse->getKey(),
        'code' => 'A-01',
        'deleted_at' => null,
    ]);
});

test('update storage area rejects a parent from its own subtree', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $child = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $storageArea->getKey(),
    ]);
    $grandChild = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $child->getKey(),
    ]);

    UpdateStorageArea::assertValidationErrors([
        'id' => $storageArea->getKey(),
        'parent_id' => $grandChild->getKey(),
    ], 'parent_id');
});

test('update storage area rejects itself as its own parent', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    UpdateStorageArea::assertValidationErrors([
        'id' => $storageArea->getKey(),
        'parent_id' => $storageArea->getKey(),
    ], 'parent_id');
});

test('update storage area rejects moving to another warehouse while its children stay behind', function (): void {
    $parent = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $other = Warehouse::factory()->create();

    UpdateStorageArea::assertValidationErrors([
        'id' => $parent->getKey(),
        'warehouse_id' => $other->getKey(),
    ], 'warehouse_id');
});

test('delete storage area', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    expect(DeleteStorageArea::make(['id' => $storageArea->getKey()])->validate()->execute())->toBeTrue();

    $this->assertSoftDeleted('storage_areas', ['id' => $storageArea->getKey()]);
});

test('delete storage area refuses while stock remains', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $product = Product::factory()->create();

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'posting' => 10,
    ]);

    DeleteStorageArea::assertValidationErrors(['id' => $storageArea->getKey()], 'stock_postings');
});

test('delete storage area refuses while stock postings reference it after netting to zero', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $product = Product::factory()->create();

    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'posting' => 10,
    ]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ]);

    DeleteStorageArea::assertValidationErrors(['id' => $storageArea->getKey()], 'stock_postings');
});

test('delete storage area refuses while it has child storage areas', function (): void {
    $parent = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);

    DeleteStorageArea::assertValidationErrors(['id' => $parent->getKey()], 'children');
});

test('delete storage area succeeds when its only child storage area is soft-deleted', function (): void {
    $parent = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $child = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $child->delete();

    expect(DeleteStorageArea::make(['id' => $parent->getKey()])->validate()->execute())->toBeTrue();
});

test('update storage area allows moving to another warehouse when its only child storage area is soft-deleted', function (): void {
    $parent = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $child = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $child->delete();
    $other = Warehouse::factory()->create();

    $updated = UpdateStorageArea::make([
        'id' => $parent->getKey(),
        'warehouse_id' => $other->getKey(),
    ])->validate()->execute();

    expect($updated->warehouse_id)->toBe($other->getKey());
});

test('delete storage area without validation fails on a missing storage area', function (): void {
    DeleteStorageArea::make(['id' => 999999])->execute();
})->throws(ModelNotFoundException::class);

test('update storage area without validation fails on a missing storage area', function (): void {
    UpdateStorageArea::make(['id' => 999999, 'name' => 'Ghost'])->execute();
})->throws(ModelNotFoundException::class);

test('create storage area pools every validation failure', function (): void {
    $other = Warehouse::factory()->create();
    $foreignParent = StorageArea::factory()->create(['warehouse_id' => $other->getKey()]);
    StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'code' => 'A-01']);

    CreateStorageArea::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'parent_id' => $foreignParent->getKey(),
        'code' => 'A-01',
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ], ['parent_id', 'code']);
});
