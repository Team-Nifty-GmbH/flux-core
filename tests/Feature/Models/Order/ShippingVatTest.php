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
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
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

    $this->warehouse = Warehouse::factory()->create();
    $this->reduced = VatRate::factory()->create(['rate_percentage' => 0.07]);
    $this->standard = VatRate::factory()->create(['rate_percentage' => 0.19]);

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

    $this->addPosition = function (VatRate $vatRate, string $unitPrice, bool $isShipping = false, bool $isNet = false): OrderPosition {
        $product = Product::factory()->create([
            'vat_rate_id' => $vatRate->getKey(),
            'is_shipping_item' => $isShipping,
        ]);

        return CreateOrderPosition::make([
            'order_id' => $this->order->getKey(),
            'product_id' => $product->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => $isShipping ? 'Shipping' : 'Goods',
            'vat_rate_id' => $vatRate->getKey(),
            'amount' => 1,
            'unit_price' => $unitPrice,
            'is_net' => $isNet,
        ])
            ->validate()
            ->execute();
    };

    $this->recalculate = function (): Order {
        $order = $this->order->refresh();
        $order->calculatePrices()->save();

        return $order->refresh();
    };
});

test('shipping takes the rate of goods that all share one rate', function (): void {
    ($this->addPosition)($this->reduced, '10.70');
    $shipping = ($this->addPosition)($this->standard, '5.50', true);

    $order = ($this->recalculate)();
    $shipping->refresh();

    expect($shipping->vat_rate_id)->toBe($this->reduced->getKey())
        ->and(bcround($shipping->total_gross_price, 2))->toEqual('5.50')
        ->and(bcround($shipping->total_net_price, 2))->toEqual('5.14')
        ->and(array_column($order->total_vats, 'vat_rate_id'))->toBe([$this->reduced->getKey()]);
});

test('shipping keeps its rate when the goods have the same rate', function (): void {
    ($this->addPosition)($this->standard, '11.90');
    $shipping = ($this->addPosition)($this->standard, '5.50', true);

    ($this->recalculate)();
    $shipping->refresh();

    expect($shipping->vat_rate_id)->toBe($this->standard->getKey())
        ->and(bcround($shipping->total_net_price, 2))->toEqual('4.62');
});

test('shipping is split over the rates of mixed goods by their net value', function (): void {
    ($this->addPosition)($this->reduced, '74.90');
    ($this->addPosition)($this->standard, '35.70');
    $shipping = ($this->addPosition)($this->standard, '5.50', true);

    $order = ($this->recalculate)();
    $shipping->refresh();
    $vats = collect($order->total_vats)->keyBy('vat_rate_id');

    expect($shipping->vat_rate_id)->toBeNull()
        ->and(bcround($shipping->vat_rate_percentage, 3))->toEqual('0.106')
        ->and(bcround($shipping->total_gross_price, 2))->toEqual('5.50')
        ->and($vats)->toHaveCount(2)
        ->and(bcround($vats[$this->reduced->getKey()]['total_net_price'], 2))->toEqual('73.48')
        ->and(bcround($vats[$this->standard->getKey()]['total_net_price'], 2))->toEqual('31.49')
        ->and(bcround($order->total_gross_price, 2))->toEqual('116.10');
});

test('net entered shipping keeps its net amount', function (): void {
    ($this->addPosition)($this->reduced, '10.70');
    $shipping = ($this->addPosition)($this->standard, '4.62', true, true);

    ($this->recalculate)();
    $shipping->refresh();

    expect($shipping->vat_rate_id)->toBe($this->reduced->getKey())
        ->and(bcround($shipping->total_net_price, 2))->toEqual('4.62')
        ->and(bcround($shipping->total_gross_price, 2))->toEqual('4.94');
});

test('shipping without goods keeps its own rate', function (): void {
    $shipping = ($this->addPosition)($this->standard, '5.50', true);

    ($this->recalculate)();

    expect($shipping->refresh()->vat_rate_id)->toBe($this->standard->getKey());
});

test('shipping falls back to its own rate once the goods are gone', function (): void {
    $goods = ($this->addPosition)($this->reduced, '10.70');
    $shipping = ($this->addPosition)($this->standard, '5.50', true);
    ($this->recalculate)();

    $goods->delete();
    ($this->recalculate)();
    $shipping->refresh();

    expect($shipping->vat_rate_id)->toBe($this->standard->getKey())
        ->and(bcround($shipping->total_gross_price, 2))->toEqual('5.50')
        ->and(bcround($shipping->total_net_price, 2))->toEqual('4.62');
});

test('an invoiced order keeps the shipping rate it was invoiced with', function (): void {
    Order::query()->whereKey($this->order->getKey())->update(['invoice_number' => 'RE-1']);
    ($this->addPosition)($this->reduced, '74.90');
    ($this->addPosition)($this->standard, '35.70');
    $shipping = ($this->addPosition)($this->standard, '5.50', true);

    $order = ($this->recalculate)();

    expect($shipping->refresh()->vat_rate_id)->toBe($this->standard->getKey())
        ->and(bcround($shipping->total_net_price, 2))->toEqual('4.62')
        ->and(collect($order->total_vats)->firstWhere('vat_rate_id', $this->standard->getKey())['total_net_price'])
        ->toEqual('34.62');
});
