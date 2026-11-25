<?php

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\RebateAgreement\SettleRebateAgreement;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\RebateAgreement;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
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

    $this->refundOrderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Refund,
        'is_active' => true,
    ]);

    $this->agreement = RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'order_type_id' => $this->refundOrderType->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [
            ['from_volume' => 50000, 'percentage' => 0.02],
            ['from_volume' => 100000, 'percentage' => 0.03],
        ],
    ]);

    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'currency_id' => $this->currency->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'address_delivery_id' => $this->address->getKey(),
        'invoice_number' => 'RE-1',
        'invoice_date' => '2025-06-01',
    ]);

    foreach ([
        ['net' => 100000, 'rate' => 0.19, 'vat_rate_id' => $this->vatFull->getKey()],
        ['net' => 20000, 'rate' => 0.07, 'vat_rate_id' => $this->vatReduced->getKey()],
    ] as $index => $position) {
        $order->orderPositions()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'vat_rate_id' => data_get($position, 'vat_rate_id'),
            'product_id' => Product::factory()->create()->getKey(),
            'amount' => 1,
            'name' => 'Position ' . $index,
            'unit_net_price' => data_get($position, 'net'),
            'total_net_price' => data_get($position, 'net'),
            'total_base_net_price' => data_get($position, 'net'),
            'vat_rate_percentage' => data_get($position, 'rate'),
            'slug_position' => str_pad((string) $index, 8, '0', STR_PAD_LEFT),
            'sort_number' => $index,
        ]);
    }

    $order->calculatePrices()->save();
});

test('settling creates a refund order with one negative position per vat rate', function (): void {
    $order = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->orderType->order_type_enum)->toBe(OrderTypeEnum::Refund)
        ->and($order->contact_id)->toBe($this->contact->getKey());

    $positions = $order->orderPositions()->orderBy('sort_number')->get();

    expect($positions)->toHaveCount(2);

    // The refund multiplier signs the totals, so the rebate reduces the revenue.
    expect($positions->pluck('total_net_price')->map(fn ($value) => bcround($value, 2))->all())
        ->toBe(['-3000.00', '-600.00']);

    expect(bcround($order->total_net_price, 2))->toBe('-3600.00');
});

test('settling records the created order on the agreement', function (): void {
    $order = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    $this->agreement->refresh();

    expect($this->agreement->rebate_order_id)->toBe($order->getKey())
        ->and($this->agreement->settled_at)->not->toBeNull();
});

test('the position names carry the period and the granted percentage', function (): void {
    $order = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($order->orderPositions()->value('name'))
        ->toContain($this->agreement->period_start->locale(app()->getLocale())->isoFormat('L'))
        ->toContain($this->agreement->period_end->locale(app()->getLocale())->isoFormat('L'))
        ->toContain('3');
});

test('revenue without a vat rate blocks the settlement', function (): void {
    Order::query()
        ->where('contact_id', $this->contact->getKey())
        ->first()
        ->orderPositions()
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'amount' => 1,
            'name' => 'Tax free position',
            'unit_net_price' => 10000,
            'total_net_price' => 10000,
            'total_base_net_price' => 10000,
            'slug_position' => '00000005',
            'sort_number' => 5,
        ]);

    try {
        SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
            ->validate()
            ->execute();

        $this->fail('ValidationException expected.');
    } catch (ValidationException) {
    }

    expect($this->agreement->refresh()->settled_at)->toBeNull();
});

test('an already settled agreement cannot be settled twice', function (): void {
    SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('deleting the refund order allows settling the agreement again', function (): void {
    $firstOrder = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    DeleteOrder::make(['id' => $firstOrder->getKey()])
        ->validate()
        ->execute();

    $secondOrder = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($secondOrder->getKey())->not->toBe($firstOrder->getKey())
        ->and(bcround($secondOrder->total_net_price, 2))->toBe('-3600.00')
        ->and($this->agreement->refresh()->rebate_order_id)->toBe($secondOrder->getKey());
});

test('the refund order is created on the default tenant of the contact', function (): void {
    $this->dbTenant->update(['is_default' => false]);
    $defaultTenant = Tenant::factory()->create(['is_default' => true]);
    $this->contact->tenants()->attach($defaultTenant);
    $this->paymentType->tenants()->attach($defaultTenant);
    $this->contact->update(['payment_type_id' => $this->paymentType->getKey()]);

    $order = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($order->tenant_id)->toBe($defaultTenant->getKey());
});

test('settling an agreement whose volume reaches no tier fails without creating an order', function (): void {
    $this->agreement->update(['tiers' => [['from_volume' => 500000, 'percentage' => 0.02]]]);

    $orderCount = Order::query()->count();

    try {
        SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
            ->validate()
            ->execute();

        $this->fail('ValidationException expected.');
    } catch (ValidationException) {
    }

    $this->agreement->refresh();

    expect($this->agreement->settled_at)->toBeNull()
        ->and(Order::query()->count())->toBe($orderCount);
});

test('the refund order is created in the currency of the settled orders', function (): void {
    $order = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($order->currency_id)->toBe($this->currency->getKey());
});

test('the order type of the agreement must be a refund order type', function (): void {
    $this->agreement->update([
        'order_type_id' => OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order])->getKey(),
    ]);

    SettleRebateAgreement::assertValidationErrors(['id' => $this->agreement->getKey()], 'order_type_id');
});

test('an inactive refund order type is rejected', function (): void {
    $this->refundOrderType->update(['is_active' => false]);

    SettleRebateAgreement::assertValidationErrors(['id' => $this->agreement->getKey()], 'order_type_id');
});

test('all reasons against settling are reported together', function (): void {
    $this->refundOrderType->update(['is_active' => false]);
    $this->agreement->update(['tiers' => [['from_volume' => 500000, 'percentage' => 0.02]]]);

    try {
        SettleRebateAgreement::make(['id' => $this->agreement->getKey()])->validate();

        $this->fail('ValidationException expected.');
    } catch (ValidationException $e) {
        expect($e->errors())
            ->toHaveKeys(['id', 'order_type_id'])
            ->and($e->errors()['id'])->toBe(['The volume does not reach any tier of this rebate agreement.']);
    }
});

test('mixed currencies within the period block the settlement', function (): void {
    $order = Order::query()->where('contact_id', $this->contact->getKey())->first();
    $chfOrder = $order->replicate(['uuid', 'order_number', 'invoice_number']);
    $chfOrder->fill([
        'currency_id' => Currency::factory()->create(['iso' => 'CHF', 'is_default' => false])->getKey(),
        'invoice_number' => 'RE-CHF',
    ]);
    $chfOrder->save();

    try {
        SettleRebateAgreement::make(['id' => $this->agreement->getKey()])->validate();

        $this->fail('ValidationException expected.');
    } catch (ValidationException $e) {
        expect($e->errors()['id'])->toBe(['The orders within the period use different currencies.']);
    }
});

test('the position names follow the language of the invoice address, not the user', function (): void {
    $german = Language::factory()->create(['language_code' => 'de']);
    $this->address->update(['language_id' => $german->getKey()]);
    $this->contact->update(['invoice_address_id' => $this->address->getKey()]);
    app()->setLocale('en');

    $order = SettleRebateAgreement::make(['id' => $this->agreement->getKey()])
        ->validate()
        ->execute();

    expect($order->orderPositions()->value('name'))
        ->toMatch('/^Bonus 3\s%/u')
        ->toContain('01.01.2025')
        ->toContain('31.12.2025');
});
