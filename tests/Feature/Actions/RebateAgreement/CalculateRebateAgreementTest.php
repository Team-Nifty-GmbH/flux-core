<?php

use FluxErp\Actions\RebateAgreement\CalculateRebateAgreement;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\RebateAgreement;
use FluxErp\Models\VatRate;
use FluxErp\States\Order\Canceled;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

function rebateOrder(array $positions, array $attributes = []): Order
{
    $orderType = data_get($attributes, 'order_type')
        ?? OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);

    $order = Order::factory()->create(array_merge([
        'tenant_id' => test()->dbTenant->getKey(),
        'contact_id' => test()->contact->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => test()->paymentType->getKey(),
        'price_list_id' => test()->priceList->getKey(),
        'currency_id' => test()->currency->getKey(),
        'address_invoice_id' => test()->address->getKey(),
        'address_delivery_id' => test()->address->getKey(),
        'invoice_number' => 'RE-' . fake()->unique()->numberBetween(1, 999999),
        'invoice_date' => '2025-06-01',
        'shipping_costs_net_price' => 0,
    ], Arr::except($attributes, ['order_type'])));

    // PriceCalculation signs the totals with the order type multiplier, which creating the
    // positions directly bypasses.
    $multiplier = $orderType->order_type_enum->multiplier();

    foreach ($positions as $index => $position) {
        $net = bcmul(data_get($position, 'net'), $multiplier, 9);

        $order->orderPositions()->create([
            'tenant_id' => test()->dbTenant->getKey(),
            'vat_rate_id' => data_get($position, 'vat_rate_id'),
            'product_id' => Product::factory()->create()->getKey(),
            'amount' => 1,
            'name' => 'Position ' . $index,
            'unit_net_price' => data_get($position, 'net'),
            'total_net_price' => $net,
            'total_base_net_price' => $net,
            'vat_rate_percentage' => data_get($position, 'rate'),
            'slug_position' => str_pad((string) $index, 8, '0', STR_PAD_LEFT),
            'sort_number' => $index,
        ]);
    }

    return $order->calculatePrices()->fresh();
}

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'company' => 'Rebate Customer GmbH',
    ]);
    $this->contact->update(['main_address_id' => $this->address->getKey()]);

    $this->priceList = PriceList::factory()->create(['is_net' => true]);
    $this->currency = Currency::query()->where('iso', 'EUR')->first()
        ?? Currency::factory()->create(['iso' => 'EUR', 'is_default' => true]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['is_default' => false]);

    $this->vatFull = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->vatReduced = VatRate::factory()->create(['rate_percentage' => 0.07]);

    $this->agreement = RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [
            ['from_volume' => 50000, 'percentage' => 0.02],
            ['from_volume' => 100000, 'percentage' => 0.03],
        ],
    ]);
});

test('calculates the volume and the reached tier', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);

    $result = CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($result)
        ->volume->toBe('120000.00')
        ->percentage->toBe('0.03')
        ->total_net_price->toBe('3600.00');
});

test('splits the rebate across the vat rates by their share of the volume', function (): void {
    rebateOrder([
        ['net' => 100000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()],
        ['net' => 20000, 'rate' => 0.07, 'vat_rate_id' => $this->vatReduced->getKey()],
    ]);

    $result = CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($result->volume)->toBe('120000.00')
        ->and($result->total_net_price)->toBe('3600.00');

    expect(collect($result->positions)->map(fn (array $position) => [
        (float) data_get($position, 'vat_rate_percentage'),
        data_get($position, 'total_net_price'),
    ])->all())
        ->toBe([[0.19, '3000.00'], [0.07, '600.00']]);

    expect(collect($result->positions)->pluck('vat_rate_id')->all())
        ->toBe([$this->vatFull->getKey(), $this->vatReduced->getKey()]);
});

test('the split reconciles to the total rebate when the shares do not divide evenly', function (): void {
    rebateOrder([
        ['net' => 33333.33, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()],
        ['net' => 33333.33, 'rate' => 0.07, 'vat_rate_id' => $this->vatReduced->getKey()],
        ['net' => 33333.34, 'rate' => 0, 'vat_rate_id' => null],
    ]);

    $result = CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    $sum = collect($result->positions)->reduce(
        fn (string $carry, array $position) => bcadd($carry, data_get($position, 'total_net_price'), 2),
        '0'
    );

    expect($sum)->toBe($result->total_net_price);
});

test('retoures and refunds reduce the volume', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);
    rebateOrder(
        [['net' => 30000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]],
        ['order_type' => OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Retoure])]
    );

    $result = CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($result->volume)->toBe('90000.00')
        ->and($result->percentage)->toBe('0.02');
});

test('orders outside the period do not count', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);
    rebateOrder(
        [['net' => 80000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]],
        ['invoice_date' => '2024-12-31']
    );

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->volume)
        ->toBe('120000.00');
});

test('orders without an invoice number do not count', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);
    rebateOrder(
        [['net' => 80000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]],
        ['invoice_number' => null, 'invoice_date' => null]
    );

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->volume)
        ->toBe('120000.00');
});

test('cancelled orders do not count even when they carry an invoice number', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);
    $cancelled = rebateOrder([['net' => 80000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);
    $cancelled->state = Canceled::class;
    $cancelled->save();

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->volume)
        ->toBe('120000.00');
});

test('a previously settled rebate does not reduce the volume of a later one', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);

    $previousRebate = rebateOrder(
        [['net' => 3600, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]],
        ['order_type' => OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Refund])]
    );

    RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'rebate_order_id' => $previousRebate->getKey(),
        'settled_at' => now(),
    ]);

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->volume)
        ->toBe('120000.00');
});

test('a free text parent aggregating its children does not double the volume', function (): void {
    $order = rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);

    $child = $order->orderPositions()->first();
    $parent = $order->orderPositions()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'amount' => 1,
        'name' => 'Summary Parent',
        'is_free_text' => true,
        'total_net_price' => 120000,
        'total_base_net_price' => 120000,
        'vat_rate_percentage' => 0.19,
        'slug_position' => '00000009',
        'sort_number' => 9,
    ]);
    $child->update(['parent_id' => $parent->getKey()]);

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->volume)
        ->toBe('120000.00');
});

test('no rebate below the lowest tier', function (): void {
    rebateOrder([['net' => 40000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);

    $result = CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($result->volume)->toBe('40000.00')
        ->and($result->percentage)->toBeNull()
        ->and($result->total_net_price)->toBe('0.00')
        ->and($result->positions)->toBeEmpty();
});

test('mixed currencies within the period fail loudly', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);
    rebateOrder(
        [['net' => 10000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]],
        ['currency_id' => Currency::factory()->create(['iso' => 'CHF', 'is_default' => false])->getKey()]
    );

    CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('the calculation carries the currency of the orders', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->currency_id)
        ->toBe($this->currency->getKey());
});

test('another contact does not contribute to the volume', function (): void {
    rebateOrder([['net' => 120000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]]);

    $otherContact = Contact::factory()->create();
    $otherAddress = Address::factory()->create(['contact_id' => $otherContact->getKey()]);
    rebateOrder(
        [['net' => 80000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()]],
        [
            'contact_id' => $otherContact->getKey(),
            'address_invoice_id' => $otherAddress->getKey(),
            'address_delivery_id' => $otherAddress->getKey(),
        ]
    );

    expect(CalculateRebateAgreement::make(['id' => $this->agreement->getKey()])->validate()->execute()->volume)
        ->toBe('120000.00');
});
