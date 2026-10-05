<?php

use FluxErp\Actions\StockPosting\CreateStockPostingsFromOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $this->address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create(['is_nos' => false]);

    $this->makeOrder = function (OrderTypeEnum $orderTypeEnum, string $amount): Order {
        $order = Order::factory()->create([
            'address_invoice_id' => $this->address->getKey(),
            'contact_id' => $this->address->contact_id,
            'currency_id' => Currency::default()->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'order_type_id' => OrderType::factory()->create([
                'order_type_enum' => $orderTypeEnum,
                'is_active' => true,
            ])->getKey(),
            'payment_type_id' => PaymentType::default()->getKey(),
            'price_list_id' => PriceList::default()->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
        ]);

        OrderPosition::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $this->product->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'amount' => $amount,
            'unit_net_price' => 12.5,
            'is_free_text' => false,
            'is_alternative' => false,
        ]);

        return $order;
    };
});

test('a purchase order books its amount into stock', function (): void {
    $order = ($this->makeOrder)(OrderTypeEnum::Purchase, '3');

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    $stockPosting = StockPosting::query()
        ->where('product_id', $this->product->getKey())
        ->sole();

    expect($stockPosting->posting)->toEqual(3)
        ->and($stockPosting->purchase_price)->toEqual(12.5);
});

test('a sales order takes its amount out of the oldest stock', function (): void {
    $oldest = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 2,
        'remaining_stock' => 2,
        'reserved_stock' => 0,
        'purchase_price' => 10,
    ]);
    $newest = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
        'remaining_stock' => 5,
        'reserved_stock' => 0,
        'purchase_price' => 12,
    ]);
    $order = ($this->makeOrder)(OrderTypeEnum::Order, '3');

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    $withdrawals = StockPosting::query()
        ->where('order_position_id', $order->orderPositions()->value('id'))
        ->get()
        ->keyBy('parent_id');

    expect($oldest->refresh()->remaining_stock)->toEqual(0)
        ->and($newest->refresh()->remaining_stock)->toEqual(4)
        ->and($withdrawals[$oldest->getKey()]->purchase_price)->toEqual(10)
        ->and($withdrawals[$newest->getKey()]->purchase_price)->toEqual(12);
});

test('posting reserved stock books the unit purchase price', function (): void {
    $stock = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 4,
        'remaining_stock' => 4,
        'reserved_stock' => 0,
        'purchase_price' => 10,
    ]);
    $order = ($this->makeOrder)(OrderTypeEnum::Order, '3');

    CreateStockPostingsFromOrder::make(['id' => $order->getKey(), 'only_reserve_stock' => true])
        ->validate()
        ->execute();
    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    $withdrawal = StockPosting::query()
        ->where('order_position_id', $order->orderPositions()->value('id'))
        ->sole();

    expect($withdrawal->posting)->toEqual(-3)
        ->and($withdrawal->purchase_price)->toEqual(10)
        ->and($stock->refresh()->remaining_stock)->toEqual(1);
});

test('a retoure books its amount back into stock', function (): void {
    $order = ($this->makeOrder)(OrderTypeEnum::Retoure, '2');

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    expect(StockPosting::query()->where('product_id', $this->product->getKey())->sum('posting'))
        ->toEqual(2);
});

test('refunds and subscriptions book no stock', function (OrderTypeEnum $orderTypeEnum): void {
    $stock = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
        'remaining_stock' => 5,
        'reserved_stock' => 0,
    ]);
    $order = ($this->makeOrder)($orderTypeEnum, '2');

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])
        ->validate()
        ->execute();

    expect(StockPosting::query()->where('product_id', $this->product->getKey())->count())->toBe(1)
        ->and($stock->refresh()->remaining_stock)->toEqual(5);
})->with([
    'refund' => OrderTypeEnum::Refund,
    'purchase refund' => OrderTypeEnum::PurchaseRefund,
    'purchase subscription' => OrderTypeEnum::PurchaseSubscription,
    'subscription' => OrderTypeEnum::Subscription,
]);
