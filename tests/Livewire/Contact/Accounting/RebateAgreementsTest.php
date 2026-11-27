<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Contact\Accounting\RebateAgreements;
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
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
});

test('renders successfully', function (): void {
    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->assertOk();
});

test('only shows the agreements of the given contact', function (): void {
    $own = RebateAgreement::factory()->create(['contact_id' => $this->contact->getKey()]);
    RebateAgreement::factory()->create(['contact_id' => Contact::factory()->create()->getKey()]);

    $component = Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->assertOk()
        ->instance();

    $method = new ReflectionMethod($component, 'getBuilder');

    expect($method->invoke($component, RebateAgreement::query())->pluck('id')->all())
        ->toBe([$own->getKey()]);
});

test('editing a new agreement prefills the contact', function (): void {
    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('edit')
        ->assertHasNoErrors()
        ->assertSet('rebateAgreementForm.contact_id', $this->contact->getKey());
});

test('can create an agreement with tiers', function (): void {
    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('edit')
        ->set('rebateAgreementForm.order_type_id', OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Refund,
            'is_active' => true,
        ])->getKey())
        ->set('rebateAgreementForm.name', 'Annual Bonus')
        ->set('rebateAgreementForm.period_start', '2025-01-01')
        ->set('rebateAgreementForm.period_end', '2025-12-31')
        ->set('rebateAgreementForm.tiers', [['from_volume' => 50000, 'percentage' => 3]])
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $agreement = RebateAgreement::query()->where('name', 'Annual Bonus')->first();

    expect($agreement->contact_id)->toBe($this->contact->getKey());

    // The form shows percentages as 0..100, the action stores them as 0..1.
    expect(bcround(data_get($agreement->tiers, '0.percentage'), 2))->toBe('0.03');
});

test('save fails without tiers', function (): void {
    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('edit')
        ->set('rebateAgreementForm.name', 'No Tiers')
        ->set('rebateAgreementForm.period_start', '2025-01-01')
        ->set('rebateAgreementForm.period_end', '2025-12-31')
        ->set('rebateAgreementForm.tiers', [])
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});

test('saving keeps tiers changed elsewhere that the form did not touch', function (): void {
    $agreement = RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'tiers' => [['from_volume' => 50000, 'percentage' => 0.02]],
    ]);

    $component = Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('edit', $agreement->getKey())
        ->assertSet('rebateAgreementForm.tiers.0.percentage', '2.00');

    $agreement->update(['tiers' => [['from_volume' => 80000, 'percentage' => 0.04]]]);

    $component->set('rebateAgreementForm.name', 'Renamed')
        ->call('save')
        ->assertReturned(true);

    $agreement->refresh();

    expect($agreement->name)->toBe('Renamed')
        ->and(data_get($agreement->tiers, '0.from_volume'))->toEqual(80000)
        ->and(bcround(data_get($agreement->tiers, '0.percentage'), 2))->toBe('0.04');
});

test('calculating a settled agreement opens no preview', function (): void {
    $agreement = RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'rebate_order_id' => Order::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_id' => OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Refund])->getKey(),
            'currency_id' => Currency::factory()->create()->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'address_invoice_id' => Address::factory()->create(['contact_id' => $this->contact->getKey()])->getKey(),
        ])->getKey(),
        'settled_at' => now(),
    ]);

    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('calculate', $agreement->getKey())
        ->assertSet('calculation', null)
        ->assertToastNotification(type: 'error');
});

test('rows report whether the refund order still exists', function (): void {
    $settledAgreement = fn () => RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'rebate_order_id' => Order::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_id' => OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Refund])->getKey(),
            'currency_id' => Currency::factory()->create()->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'address_invoice_id' => Address::factory()->create(['contact_id' => $this->contact->getKey()])->getKey(),
        ])->getKey(),
        'settled_at' => now(),
    ]);
    $settled = $settledAgreement();
    $reopened = $settledAgreement();
    $reopened->rebateOrder->delete();

    $rows = collect(
        data_get(
            Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
                ->call('loadData')
                ->instance()
                ->getDataForTesting(),
            'data'
        )
    )->pluck('rebate_order_exists', 'id');

    expect($rows->get($settled->getKey()))->toBeTrue()
        ->and($rows->get($reopened->getKey()))->toBeFalse();
});

test('can delete an unsettled agreement', function (): void {
    $agreement = RebateAgreement::factory()->create(['contact_id' => $this->contact->getKey()]);

    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('delete', $agreement->getKey())
        ->assertHasNoErrors();

    $this->assertSoftDeleted('rebate_agreements', ['id' => $agreement->getKey()]);
});

test('a settled agreement cannot be deleted', function (): void {
    $agreement = RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'rebate_order_id' => Order::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_id' => OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Refund])->getKey(),
            'currency_id' => Currency::factory()->create()->getKey(),
            'price_list_id' => PriceList::factory()->create()->getKey(),
            'address_invoice_id' => Address::factory()->create(['contact_id' => $this->contact->getKey()])->getKey(),
        ])->getKey(),
        'settled_at' => now(),
    ]);

    Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('delete', $agreement->getKey())
        ->assertOk()
        ->assertReturned(false);

    $this->assertNotSoftDeleted('rebate_agreements', ['id' => $agreement->getKey()]);
});

test('calculating shows the volume and settling redirects to the created refund order', function (): void {
    $address = Address::factory()->create(['contact_id' => $this->contact->getKey()]);
    $this->contact->update(['main_address_id' => $address->getKey()]);

    $currency = Currency::query()->where('iso', 'EUR')->first()
        ?? Currency::factory()->create(['iso' => 'EUR', 'is_default' => true]);
    $priceList = PriceList::factory()->create(['is_net' => true]);
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['is_default' => false]);
    $vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $refundOrderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Refund,
        'is_active' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'invoice_number' => 'RE-LW-1',
        'invoice_date' => '2025-06-01',
    ]);
    $order->orderPositions()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'product_id' => Product::factory()->create()->getKey(),
        'amount' => 1,
        'name' => 'Position',
        'unit_net_price' => 120000,
        'total_net_price' => 120000,
        'total_base_net_price' => 120000,
        'vat_rate_percentage' => 0.19,
        'slug_position' => '00000001',
        'sort_number' => 0,
    ]);
    $order->calculatePrices()->save();

    $agreement = RebateAgreement::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'order_type_id' => $refundOrderType->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [['from_volume' => 100000, 'percentage' => 0.03]],
    ]);

    $component = Livewire::test(RebateAgreements::class, ['contactId' => $this->contact->getKey()])
        ->call('calculate', $agreement->getKey())
        ->assertHasNoErrors()
        ->assertSet('calculation.volume', '120000.00')
        ->assertSet('calculation.total_net_price', '3600.00')
        ->call('settle');

    $agreement->refresh();

    expect($agreement->settled_at)->not->toBeNull();

    $component->assertRedirect(route('orders.id', ['id' => $agreement->rebate_order_id]));
});
