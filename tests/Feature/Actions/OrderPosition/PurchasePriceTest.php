<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    PriceList::query()
        ->whereKey(PriceList::default()->getKey())
        ->update(['is_purchase' => false]);

    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create([
        'vat_rate_id' => VatRate::factory()->create(['rate_percentage' => 0.19])->getKey(),
    ]);
    Price::factory()->create([
        'price_list_id' => PriceList::default()->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 100,
    ]);
    $this->purchasePriceList = PriceList::factory()->create([
        'is_net' => true,
        'is_purchase' => true,
    ]);

    $this->order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_locked' => false,
    ]);

    $this->addPosition = fn (): OrderPosition => CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_id' => $this->warehouse->getKey(),
        'amount' => 3,
        'unit_price' => 100,
        'is_net' => true,
    ])
        ->validate()
        ->execute()
        ->refresh();

    $this->addSupplier = fn (?string $purchasePrice) => $this->product->suppliers()->attach(
        Contact::factory()->create()->getKey(),
        ['purchase_price' => $purchasePrice]
    );
});

test('a product without purchase price gets no purchase price, not its sales price', function (): void {
    $position = ($this->addPosition)();

    expect($position->purchase_price)->toBeNull()
        ->and(bcround($position->margin, 2))->toEqual('300.00');
});

test('the purchase price list price is taken per unit', function (): void {
    Price::factory()->create([
        'price_list_id' => $this->purchasePriceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 8,
    ]);

    $position = ($this->addPosition)();

    expect(bcround($position->purchase_price, 2))->toEqual('8.00')
        ->and(bcround($position->margin, 2))->toEqual('276.00');
});

test('the price of the only supplier beats the purchase price list', function (): void {
    Price::factory()->create([
        'price_list_id' => $this->purchasePriceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 8,
    ]);
    ($this->addSupplier)('7');
    ($this->addSupplier)(null);

    expect(bcround(($this->addPosition)()->purchase_price, 2))->toEqual('7.00');
});

test('several supplier prices fall back to the purchase price list', function (): void {
    Price::factory()->create([
        'price_list_id' => $this->purchasePriceList->getKey(),
        'product_id' => $this->product->getKey(),
        'price' => 8,
    ]);
    ($this->addSupplier)('7');
    ($this->addSupplier)('6');

    expect(bcround(($this->addPosition)()->purchase_price, 2))->toEqual('8.00');
});

test('the last purchase booked into stock beats every other price', function (): void {
    ($this->addSupplier)('7');
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 4,
        'remaining_stock' => 4,
        'purchase_price' => 10,
    ]);

    expect(bcround(($this->addPosition)()->purchase_price, 2))->toEqual('10.00');
});
