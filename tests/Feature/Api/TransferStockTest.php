<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->from = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $this->to = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    $this->sourcePosting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->from->getKey(),
        'posting' => 10,
    ]);

    $this->permission = Permission::findOrCreate('api.stock-postings.transfer.post');
});

test('transfer stock', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/stock-postings/transfer', [
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->to->getKey(),
        'amount' => 4,
    ]);

    $response->assertNoContent();

    expect(bccomp((string) StockPosting::query()
        ->where('warehouse_bin_id', $this->to->getKey())
        ->sum('posting'), '4', 10))->toBe(0);

    expect(bccomp((string) StockPosting::query()
        ->where('warehouse_bin_id', $this->from->getKey())
        ->where('posting', '<', 0)
        ->sum('posting'), '-4', 10))->toBe(0);

    expect(bccomp(
        (string) $this->sourcePosting->refresh()->remaining_stock,
        '6',
        10
    ))->toBe(0);
});

test('transfer stock validation fails', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/stock-postings/transfer', [
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
    ]);

    $response->assertUnprocessable();
});

test('transfer stock fails when amount exceeds available stock', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/stock-postings/transfer', [
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->to->getKey(),
        'amount' => 11,
    ]);

    $response->assertUnprocessable();

    expect(bccomp(
        (string) $this->sourcePosting->refresh()->remaining_stock,
        '10',
        10
    ))->toBe(0);
});
