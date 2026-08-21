<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\StockPosting\CreateStockPostingsFromOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->currency = Currency::factory()->create(['is_default' => true]);
    $this->language = Language::factory()->create(['is_default' => true]);
    $this->priceList = PriceList::factory()->create(['is_default' => true]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create(['is_default' => true]);
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->warehouse = Warehouse::factory()->create(['is_default' => true]);
    $this->product = Product::factory()->create(['is_nos' => false]);

    $this->makeOrder = function (OrderTypeEnum $orderTypeEnum): Order {
        $orderType = OrderType::factory()
            ->hasAttached(factory: $this->tenant, relationship: 'tenants')
            ->create(['order_type_enum' => $orderTypeEnum, 'is_active' => true]);

        return Order::factory()->create([
            'tenant_id' => $this->tenant->getKey(),
            'contact_id' => $this->contact->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'order_type_id' => $orderType->getKey(),
            'currency_id' => $this->currency->getKey(),
            'language_id' => $this->language->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'parent_id' => null,
            'order_number' => 'CH-' . fake()->unique()->numberBetween(1000, 9999),
            'is_locked' => false,
            'shipping_costs_net_price' => 0,
        ]);
    };

    $this->addPosition = function (Order $order, string|int|float $amount, ?Product $product = null): OrderPosition {
        return CreateOrderPosition::make([
            'order_id' => $order->getKey(),
            'product_id' => ($product ?? $this->product)->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'amount' => $amount,
            'unit_price' => 10,
            'is_net' => true,
        ])
            ->validate()
            ->execute();
    };

    $this->layer = function (string|int|float $posting): StockPosting {
        return StockPosting::factory()->create([
            'warehouse_id' => $this->warehouse->getKey(),
            'product_id' => $this->product->getKey(),
            'posting' => $posting,
            'purchase_price' => 5,
        ]);
    };
});

test('a purchase order does not post incoming stock and is rejected as insufficient sales stock', function (): void {
    // Documents a bug found while characterizing this action: OrderTypeEnum::multiplier()
    // is declared `: string` but its match returns `int`. Without strict_types, PHP coerces
    // the return value to the string "-1"/"1". CreateStockPostingsFromOrder is the only
    // caller in the codebase comparing that value with strict `===` against the int -1
    // (every other caller uses `==`, `bccomp()`, `>`, or `<`), so its "Handle Purchase
    // Orders" branch is unreachable and every purchase order falls through to the
    // sales-order branch instead of posting incoming stock.
    $order = ($this->makeOrder)(OrderTypeEnum::Purchase);
    ($this->addPosition)($order, 7);

    expect(fn () => CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute())
        ->toThrow(ValidationException::class);

    expect(StockPosting::query()->where('product_id', $this->product->getKey())->exists())->toBeFalse();
});

test('a sales order consumes layers in id order and decrements remaining stock', function (): void {
    $first = ($this->layer)(10);
    $second = ($this->layer)(10);

    $order = ($this->makeOrder)(OrderTypeEnum::Order);
    $position = ($this->addPosition)($order, 15);

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    $withdrawals = StockPosting::query()
        ->where('order_position_id', $position->getKey())
        ->orderBy('id')
        ->get();

    expect($withdrawals)->toHaveCount(2)
        ->and(bccomp($withdrawals[0]->posting, '-10', 10))->toBe(0)
        ->and($withdrawals[0]->parent_id)->toBe($first->getKey())
        ->and(bccomp($withdrawals[1]->posting, '-5', 10))->toBe(0)
        ->and($withdrawals[1]->parent_id)->toBe($second->getKey())
        ->and(bccomp($first->fresh()->remaining_stock, '0', 10))->toBe(0)
        ->and(bccomp($second->fresh()->remaining_stock, '5', 10))->toBe(0);
});

test('reserving moves remaining stock into reserved stock and writes the pivot', function (): void {
    $layer = ($this->layer)(10);

    $order = ($this->makeOrder)(OrderTypeEnum::Order);
    $position = ($this->addPosition)($order, 4);

    CreateStockPostingsFromOrder::make([
        'id' => $order->getKey(),
        'only_reserve_stock' => true,
    ])
        ->validate()
        ->execute();

    $layer->refresh();

    expect(bccomp($layer->remaining_stock, '6', 10))->toBe(0)
        ->and(bccomp($layer->reserved_stock, '4', 10))->toBe(0)
        ->and(StockPosting::query()->where('order_position_id', $position->getKey())->count())->toBe(0)
        ->and(bccomp($position->reservedStock()->first()->pivot->reserved_amount, '4', 10))->toBe(0);
});

test('posting after reserving consumes the reservation', function (): void {
    $layer = ($this->layer)(10);

    $order = ($this->makeOrder)(OrderTypeEnum::Order);
    $position = ($this->addPosition)($order, 4);

    CreateStockPostingsFromOrder::make(['id' => $order->getKey(), 'only_reserve_stock' => true])
        ->validate()
        ->execute();
    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    $layer->refresh();

    expect(bccomp($layer->reserved_stock, '0', 10))->toBe(0)
        ->and(bccomp((string) $position->stockPostings()->sum('posting'), '-4', 10))->toBe(0)
        ->and($position->reservedStock()->count())->toBe(0);
});

test('a never out of stock product posts beyond the available layers', function (): void {
    $nosProduct = Product::factory()->create(['is_nos' => true]);
    $order = ($this->makeOrder)(OrderTypeEnum::Order);

    $position = ($this->addPosition)($order, 3, $nosProduct);

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    $posting = StockPosting::query()->where('order_position_id', $position->getKey())->first();

    expect(bccomp($posting->posting, '-3', 10))->toBe(0)
        ->and($posting->parent_id)->toBeNull();
});

test('insufficient stock on a normal product is rejected', function (): void {
    ($this->layer)(2);

    $order = ($this->makeOrder)(OrderTypeEnum::Order);
    ($this->addPosition)($order, 9);

    expect(fn () => CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute())
        ->toThrow(ValidationException::class);
});
