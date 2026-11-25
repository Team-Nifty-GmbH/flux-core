<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Lot;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Tenant;
use FluxErp\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    $this->reserveFor = function (StockPosting $layer, string|int|float $amount): OrderPosition {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()
            ->hasAttached(factory: $tenant, relationship: 'tenants')
            ->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->getKey(),
            'is_main_address' => true,
        ]);
        $orderType = OrderType::factory()
            ->hasAttached(factory: $tenant, relationship: 'tenants')
            ->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);

        $order = Order::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'contact_id' => $contact->getKey(),
            'address_invoice_id' => $address->getKey(),
            'order_type_id' => $orderType->getKey(),
            'currency_id' => Currency::factory()->create()->getKey(),
            'language_id' => Language::factory()->create()->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'payment_type_id' => PaymentType::factory()
                ->hasAttached(factory: $tenant, relationship: 'tenants')
                ->create()
                ->getKey(),
            'parent_id' => null,
            'is_locked' => false,
        ]);

        $orderPosition = OrderPosition::factory()->create([
            'order_id' => $order->getKey(),
            'tenant_id' => $tenant->getKey(),
            'product_id' => $this->product->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'is_free_text' => false,
        ]);

        $orderPosition->reservedStock()->attach($layer->getKey(), ['reserved_amount' => $amount]);

        return $orderPosition;
    };
});

test('a warehouse that requires storage areas rejects an incoming posting without one', function (): void {
    $this->warehouse->update(['requires_storage_area' => true]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
    ], 'storage_area_id');
});

test('a warehouse that requires storage areas accepts an incoming posting with one', function (): void {
    $this->warehouse->update(['requires_storage_area' => true]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->storageArea->getKey(),
        'posting' => 5,
    ])->validate()->execute();

    expect($posting->storage_area_id)->toBe($this->storageArea->getKey());
});

test('a lot tracked product rejects an incoming posting without a lot', function (): void {
    $this->product->update(['is_lot_tracked' => true]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
    ], 'lot_id');
});

test('a lot tracked product accepts an incoming posting with a lot', function (): void {
    $this->product->update(['is_lot_tracked' => true]);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
    ])->validate()->execute();

    expect($posting->lot_id)->toBe($lot->getKey());
});

test('a withdrawal may not exceed the remaining stock of its parent layer', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 4,
    ]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -9,
    ], 'posting');
});

test('a withdrawal may draw the stock its own order position reserved', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $layer->update(['remaining_stock' => 0, 'reserved_stock' => 10]);
    $orderPosition = ($this->reserveFor)($layer, 10);

    $child = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'order_position_id' => $orderPosition->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ])
        ->validate()
        ->execute();

    $this->assertDatabaseHas('stock_postings', [
        'id' => $child->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ]);
});

test('a withdrawal may not exceed the remaining plus its own reserved stock', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $layer->update(['remaining_stock' => 4, 'reserved_stock' => 6]);
    $orderPosition = ($this->reserveFor)($layer, 6);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'order_position_id' => $orderPosition->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -11,
    ], 'posting');
});

test('a withdrawal may not draw stock another order position reserved', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $layer->update(['remaining_stock' => 0, 'reserved_stock' => 10]);
    ($this->reserveFor)($layer, 10);

    $otherLayer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $other = ($this->reserveFor)($otherLayer, 10);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'order_position_id' => $other->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ], 'posting');
});

test('an outgoing posting is exempt from the storage area requirement', function (): void {
    $this->warehouse->update(['requires_storage_area' => true]);
    $nos = Product::factory()->create(['is_nos' => true]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $nos->getKey(),
        'posting' => -2,
    ])->validate()->execute();

    expect(bccomp($posting->posting, '-2', 10))->toBe(0);
});

test('an incoming posting into a storage area that is not a storage location is rejected', function (): void {
    $zone = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_storage_location' => false,
    ]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $zone->getKey(),
        'posting' => 5,
    ], 'storage_area_id');
});

test('an incoming posting into an inactive storage area is rejected', function (): void {
    $inactive = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_active' => false,
    ]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $inactive->getKey(),
        'posting' => 5,
    ], 'storage_area_id');
});
