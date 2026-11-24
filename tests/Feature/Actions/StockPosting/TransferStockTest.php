<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\TransferStock;
use FluxErp\Actions\StockPosting\UpdateStockPosting;
use FluxErp\Models\Lot;
use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use FluxErp\Support\Stock\StockAllocator;
use FluxErp\Tests\Fixtures\FallingShortStockAllocatorFixture;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->from = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $this->to = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
});

test('transferring moves stock as a posting pair and leaves the warehouse balance untouched', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 10,
        'purchase_price' => 3,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 4,
    ])->validate()->execute();

    $outgoing = StockPosting::query()
        ->where('storage_area_id', $this->from->getKey())
        ->where('posting', '<', 0)
        ->first();
    $incoming = StockPosting::query()
        ->where('storage_area_id', $this->to->getKey())
        ->first();

    expect(bccomp($outgoing->posting, '-4', 10))->toBe(0)
        ->and($outgoing->parent_id)->toBe($layer->getKey())
        ->and(bccomp($incoming->posting, '4', 10))->toBe(0)
        ->and(bccomp($incoming->remaining_stock, '4', 10))->toBe(0)
        ->and(bccomp($incoming->purchase_price, '3', 10))->toBe(0)
        ->and(bccomp($layer->fresh()->remaining_stock, '6', 10))->toBe(0)
        ->and(bccomp((string) StockPosting::query()
            ->where('warehouse_id', $this->warehouse->getKey())
            ->where('product_id', $this->product->getKey())
            ->sum('posting'), '10', 10))->toBe(0);
});

test('a transfer inherits the lot of its source layer', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    expect(StockPosting::query()->where('storage_area_id', $this->to->getKey())->first()->lot_id)
        ->toBe($lot->getKey());
});

test('a transfer spanning several layers creates one pair per layer', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 3,
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 3,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    $incoming = StockPosting::query()
        ->where('storage_area_id', $this->to->getKey())
        ->orderBy('id')
        ->get();

    expect($incoming)->toHaveCount(2)
        ->and(bccomp($incoming->get(0)->posting, '3', 10))->toBe(0)
        ->and(bccomp($incoming->get(1)->posting, '2', 10))->toBe(0)
        ->and(bccomp((string) StockPosting::query()
            ->where('storage_area_id', $this->to->getKey())
            ->sum('posting'), '5', 10))->toBe(0);
});

test('transferring more than the source storage area holds is rejected', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 2,
    ]);

    $transfer = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 9,
    ])->validate();

    expect(fn () => $transfer->execute())->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('stock_postings', [
        'storage_area_id' => $this->to->getKey(),
    ]);
});

test('a target storage area from another warehouse is rejected', function (): void {
    $other = Warehouse::factory()->create();
    $foreign = StorageArea::factory()->create(['warehouse_id' => $other->getKey()]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $foreign->getKey(),
        'amount' => 1,
    ], 'to_storage_area_id');
});

test('a target storage area that is not a storage location is rejected', function (): void {
    $zone = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_storage_location' => false,
    ]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $zone->getKey(),
        'amount' => 1,
    ], 'to_storage_area_id');
});

test('source and target storage area may not be the same', function (): void {
    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->from->getKey(),
        'amount' => 1,
    ], 'to_storage_area_id');
});

test('a transfer restricted to a lot only draws from that lot', function (): void {
    $wanted = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    $other = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $other->getKey(),
        'posting' => 5,
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $wanted->getKey(),
        'posting' => 5,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'lot_id' => $wanted->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    $incoming = StockPosting::query()
        ->where('storage_area_id', $this->to->getKey())
        ->get();

    expect($incoming)->toHaveCount(1)
        ->and($incoming->first()->lot_id)->toBe($wanted->getKey())
        ->and(bccomp($incoming->first()->posting, '5', 10))->toBe(0);
});

test('a transfer restricted to a lot that cannot cover the amount is rejected', function (): void {
    $wanted = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 10,
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $wanted->getKey(),
        'posting' => 2,
    ]);

    $transfer = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'lot_id' => $wanted->getKey(),
        'amount' => 5,
    ])->validate();

    expect(fn () => $transfer->execute())->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('stock_postings', [
        'storage_area_id' => $this->to->getKey(),
    ]);
});

test('a transfer naming a lot from a different product is rejected', function (): void {
    $otherProduct = Product::factory()->create();
    $lot = Lot::factory()->create(['product_id' => $otherProduct->getKey()]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'lot_id' => $lot->getKey(),
        'amount' => 1,
    ], 'lot_id');
});

test('an allocation that falls short between validation and execution is rejected', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 10,
    ]);

    app()->bind(StockAllocator::class, FallingShortStockAllocatorFixture::class);

    expect(fn () => TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 4,
    ])->validate()->execute())
        ->toThrow(ValidationException::class);

    expect(StockPosting::query()->where('storage_area_id', $this->to->getKey())->exists())->toBeFalse();
});

test('a transfer into another warehouse moves the running stock of both warehouses', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
        'purchase_price' => 7,
    ]);
    $otherWarehouse = Warehouse::factory()->create();
    $target = StorageArea::factory()->create(['warehouse_id' => $otherWarehouse->getKey()]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'to_warehouse_id' => $otherWarehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $target->getKey(),
        'amount' => 3,
    ])->validate()->execute();

    $incoming = StockPosting::query()->where('storage_area_id', $target->getKey())->sole();
    $latestStock = fn (Warehouse $warehouse) => (string) StockPosting::query()
        ->where('warehouse_id', $warehouse->getKey())
        ->where('product_id', $this->product->getKey())
        ->latest('id')
        ->value('stock');

    expect($incoming->warehouse_id)->toBe($otherWarehouse->getKey())
        ->and($incoming->lot_id)->toBe($lot->getKey())
        ->and(bccomp($incoming->purchase_price, '7', 10))->toBe(0)
        ->and(bccomp($incoming->remaining_stock, '3', 10))->toBe(0)
        ->and(bccomp($latestStock($this->warehouse), '2', 10))->toBe(0)
        ->and(bccomp($latestStock($otherWarehouse), '3', 10))->toBe(0)
        ->and(bccomp((string) StockPosting::query()
            ->where('storage_area_id', $this->from->getKey())
            ->sum('remaining_stock'), '2', 10))->toBe(0);
});

test('a storage area of another warehouse than the target warehouse is rejected', function (): void {
    $otherWarehouse = Warehouse::factory()->create();

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'to_warehouse_id' => $otherWarehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 1,
    ], 'to_storage_area_id');
});

test('stock outside any storage area can be transferred when the warehouse requires none', function (): void {
    $this->warehouse->update(['requires_storage_area' => false]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ]);
    $loose = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => null,
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 4,
    ])->validate()->execute();

    $outgoing = StockPosting::query()->where('posting', '<', 0)->sole();

    expect($outgoing->parent_id)->toBe($loose->getKey())
        ->and($outgoing->storage_area_id)->toBeNull()
        ->and(bccomp($loose->fresh()->remaining_stock, '1', 10))->toBe(0);
});

test('stock can be transferred out of a storage area when the target warehouse requires none', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ]);
    $otherWarehouse = Warehouse::factory()->create(['requires_storage_area' => false]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'to_warehouse_id' => $otherWarehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'amount' => 2,
    ])->validate()->execute();

    $incoming = StockPosting::query()->where('warehouse_id', $otherWarehouse->getKey())->sole();

    expect($incoming->storage_area_id)->toBeNull()
        ->and(bccomp($incoming->posting, '2', 10))->toBe(0);
});

test('a missing storage area is rejected when the warehouse requires one', function (string $field): void {
    $this->warehouse->update(['requires_storage_area' => true]);

    TransferStock::assertValidationErrors(array_merge([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 1,
    ], [$field => null]), $field);
})->with(['from_storage_area_id', 'to_storage_area_id']);

test('a transfer without storage areas inside the same warehouse is rejected', function (): void {
    $this->warehouse->update(['requires_storage_area' => false]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'amount' => 1,
    ], 'to_storage_area_id');
});

test('a transfer by serial number moves exactly those serial numbers', function (): void {
    [$first, $second] = SerialNumber::factory()->count(2)->create()->all();
    foreach ([$first, $second] as $serialNumber) {
        StockPosting::factory()->create([
            'warehouse_id' => $this->warehouse->getKey(),
            'product_id' => $this->product->getKey(),
            'storage_area_id' => $this->from->getKey(),
            'serial_number_id' => $serialNumber->getKey(),
            'posting' => 1,
        ]);
    }

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'serial_number_ids' => [$second->getKey()],
        'amount' => 1,
    ])->validate()->execute();

    expect(StockPosting::query()
        ->where('storage_area_id', $this->to->getKey())
        ->pluck('serial_number_id')
        ->all())->toBe([$second->getKey()]);
});

test('serial numbers that do not match the amount are rejected', function (): void {
    $serialNumber = SerialNumber::factory()->create();

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'serial_number_ids' => [$serialNumber->getKey()],
        'amount' => 2,
    ], 'serial_number_ids');
});

test('a serial number outside the source storage area is rejected and nothing is booked', function (): void {
    $elsewhere = SerialNumber::factory()->create();
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->to->getKey(),
        'serial_number_id' => $elsewhere->getKey(),
        'posting' => 1,
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'serial_number_id' => SerialNumber::factory()->create()->getKey(),
        'posting' => 1,
    ]);
    $count = StockPosting::query()->count();

    $transfer = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'serial_number_ids' => [$elsewhere->getKey()],
        'amount' => 1,
    ])->validate();

    expect(fn () => $transfer->execute())->toThrow(ValidationException::class)
        ->and(StockPosting::query()->count())->toBe($count);
});

test('a transfer restricted to stock postings only moves those layers', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    $foreign = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
        'purchase_price' => 2,
    ]);
    $own = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
        'purchase_price' => 7,
    ]);

    $incoming = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'stock_posting_ids' => [$own->getKey()],
        'amount' => 3,
    ])->validate()->execute();

    expect($incoming)->toHaveCount(1)
        ->and($incoming->first()->parent_id)->toBe($own->getKey())
        ->and(bccomp($incoming->first()->purchase_price, '7', 10))->toBe(0)
        ->and(bccomp($own->fresh()->remaining_stock, '2', 10))->toBe(0)
        ->and(bccomp($foreign->fresh()->remaining_stock, '5', 10))->toBe(0);
});

test('a transfer restricted to stock postings that cannot cover the amount is rejected', function (): void {
    $own = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 2,
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 10,
    ]);
    $count = StockPosting::query()->count();

    $transfer = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'stock_posting_ids' => [$own->getKey()],
        'amount' => 3,
    ])->validate();

    expect(fn () => $transfer->execute())->toThrow(ValidationException::class)
        ->and(StockPosting::query()->count())->toBe($count);
});

test('a stock posting outside the source of the transfer is rejected and nothing is booked', function (array $attributes): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ]);
    $elsewhere = StockPosting::factory()->create(array_merge([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ], $attributes));
    $count = StockPosting::query()->count();

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'stock_posting_ids' => [$elsewhere->getKey()],
        'amount' => 1,
    ], 'stock_posting_ids');

    expect(StockPosting::query()->count())->toBe($count);
})->with([
    'other storage area' => [fn () => ['storage_area_id' => $this->to->getKey()]],
    'other product' => [fn () => ['product_id' => Product::factory()->create()->getKey()]],
    'other warehouse' => [fn () => [
        'warehouse_id' => ($warehouse = Warehouse::factory()->create())->getKey(),
        'storage_area_id' => StorageArea::factory()->create(['warehouse_id' => $warehouse->getKey()])->getKey(),
    ]],
]);

test('a stock posting that does not exist is rejected', function (): void {
    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'stock_posting_ids' => [(StockPosting::query()->max('id') ?? 0) + 1],
        'amount' => 1,
    ], 'stock_posting_ids.0');
});

test('a transfer returns its incoming stock postings', function (): void {
    StockPosting::factory()->count(2)->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 3,
    ]);

    $incoming = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    expect($incoming)->toHaveCount(2)
        ->and($incoming->every(fn (StockPosting $posting) => $posting->storage_area_id === $this->to->getKey()))->toBeTrue()
        ->and(bccomp((string) $incoming->sum('posting'), '5', 10))->toBe(0);
});

test('the transfer permission alone is enough to transfer stock', function (): void {
    Permission::findOrCreate('action.' . CreateStockPosting::name(), 'web');
    Permission::findOrCreate('action.' . UpdateStockPosting::name(), 'web');
    $this->user->givePermissionTo(Permission::findOrCreate('action.' . TransferStock::name(), 'web'));
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ]);

    $incoming = TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 2,
    ])->checkPermission()->validate()->execute();

    expect(bccomp((string) $incoming->sum('posting'), '2', 10))->toBe(0);
});

test('a user without the transfer permission is refused even with stock posting permissions', function (): void {
    Permission::findOrCreate('action.' . TransferStock::name(), 'web');
    $this->user->givePermissionTo([
        Permission::findOrCreate('action.' . CreateStockPosting::name(), 'web'),
        Permission::findOrCreate('action.' . UpdateStockPosting::name(), 'web'),
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ]);

    expect(fn () => TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_storage_area_id' => $this->from->getKey(),
        'to_storage_area_id' => $this->to->getKey(),
        'amount' => 2,
    ])->checkPermission()->validate()->execute())
        ->toThrow(UnauthorizedException::class)
        ->and(StockPosting::query()->where('storage_area_id', $this->to->getKey())->exists())->toBeFalse();
});

test('a transfer given its ids as strings is accepted', function (): void {
    $toWarehouse = Warehouse::factory()->create();
    $toArea = StorageArea::factory()->create(['warehouse_id' => $toWarehouse->getKey()]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->from->getKey(),
        'posting' => 5,
    ]);

    $incoming = TransferStock::make([
        'warehouse_id' => (string) $this->warehouse->getKey(),
        'to_warehouse_id' => (string) $toWarehouse->getKey(),
        'product_id' => (string) $this->product->getKey(),
        'from_storage_area_id' => (string) $this->from->getKey(),
        'to_storage_area_id' => (string) $toArea->getKey(),
        'amount' => '2',
    ])->validate()->execute();

    expect($incoming)->toHaveCount(1)
        ->and($incoming->first()->storage_area_id)->toBe($toArea->getKey())
        ->and($incoming->first()->warehouse_id)->toBe($toWarehouse->getKey());
});
