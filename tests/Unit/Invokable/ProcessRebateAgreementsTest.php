<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessRebateAgreements;
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
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function rebateAgreementWithVolume(string $periodEnd, float $net = 120000): RebateAgreement
{
    $contact = Contact::factory()
        ->hasAttached(factory: test()->dbTenant, relationship: 'tenants')
        ->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $contact->update(['main_address_id' => $address->getKey()]);

    $order = Order::factory()->create([
        'tenant_id' => test()->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'order_type_id' => test()->orderType->getKey(),
        'payment_type_id' => test()->paymentType->getKey(),
        'price_list_id' => test()->priceList->getKey(),
        'currency_id' => test()->currency->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'invoice_number' => 'RE-' . fake()->unique()->numberBetween(1, 999999),
        'invoice_date' => '2025-06-01',
        'shipping_costs_net_price' => 0,
    ]);

    $order->orderPositions()->create([
        'tenant_id' => test()->dbTenant->getKey(),
        'vat_rate_id' => test()->vatRate->getKey(),
        'product_id' => Product::factory()->create()->getKey(),
        'amount' => 1,
        'name' => 'Position',
        'unit_net_price' => $net,
        'total_net_price' => $net,
        'total_base_net_price' => $net,
        'vat_rate_percentage' => 0.19,
        'slug_position' => '00000001',
        'sort_number' => 0,
    ]);

    $order->calculatePrices()->save();

    return RebateAgreement::factory()->create([
        'contact_id' => $contact->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => $periodEnd,
        'tiers' => [['from_volume' => 50000, 'percentage' => 0.02]],
    ]);
}

beforeEach(function (): void {
    $this->priceList = PriceList::factory()->create(['is_net' => true]);
    $this->currency = Currency::query()->where('iso', 'EUR')->first()
        ?? Currency::factory()->create(['iso' => 'EUR', 'is_default' => true]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['is_default' => false]);
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $this->refundOrderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Refund,
        'is_active' => true,
    ]);
});

test('settles a due agreement', function (): void {
    $agreement = rebateAgreementWithVolume('2025-12-31');

    expect((new ProcessRebateAgreements())(orderTypeId: $this->refundOrderType->getKey()))->toBeTrue();

    expect($agreement->refresh()->settled_at)->not->toBeNull()
        ->and($agreement->rebate_order_id)->not->toBeNull();
});

test('does not settle an agreement whose period has not ended', function (): void {
    $agreement = rebateAgreementWithVolume(now()->addMonth()->toDateString());

    (new ProcessRebateAgreements())(orderTypeId: $this->refundOrderType->getKey());

    expect($agreement->refresh()->settled_at)->toBeNull();
});

test('does not settle an inactive agreement', function (): void {
    $agreement = rebateAgreementWithVolume('2025-12-31');
    $agreement->update(['is_active' => false]);

    (new ProcessRebateAgreements())(orderTypeId: $this->refundOrderType->getKey());

    expect($agreement->refresh()->settled_at)->toBeNull();
});

test('running twice settles an agreement only once', function (): void {
    $agreement = rebateAgreementWithVolume('2025-12-31');

    (new ProcessRebateAgreements())(orderTypeId: $this->refundOrderType->getKey());
    $rebateOrderId = $agreement->refresh()->rebate_order_id;

    (new ProcessRebateAgreements())(orderTypeId: $this->refundOrderType->getKey());

    expect($agreement->refresh()->rebate_order_id)->toBe($rebateOrderId)
        ->and(Order::query()->whereKeyNot($rebateOrderId)->where('order_type_id', $this->refundOrderType->getKey())
            ->exists())
        ->toBeFalse();
});

test('a failing agreement does not stop the remaining ones', function (): void {
    $failing = rebateAgreementWithVolume('2025-12-31');
    $failing->update(['tiers' => []]);
    $succeeding = rebateAgreementWithVolume('2025-12-31');

    expect((new ProcessRebateAgreements())(orderTypeId: $this->refundOrderType->getKey()))->toBeTrue();

    expect($succeeding->refresh()->settled_at)->not->toBeNull();
});
