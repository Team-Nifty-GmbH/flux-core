<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Livewire\Livewire;
use function Livewire\invade;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $this->testOrder = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(ReplicateOrderPositionList::class, ['orderId' => $this->testOrder->getKey()])
        ->assertOk();
});

test('getResultFromQuery returns data key with positions', function (): void {
    $vatRate = VatRate::factory()->create();

    OrderPosition::factory()->create([
        'order_id' => $this->testOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'ReplicateTestPosition',
        'amount' => 5,
        'signed_amount' => 5,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 500,
        'total_gross_price' => 595,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $instance = invade(
        Livewire::test(ReplicateOrderPositionList::class, [
            'orderId' => $this->testOrder->getKey(),
        ])->instance()
    );

    $query = $instance->getBuilder(OrderPosition::query());
    $result = $instance->getResultFromQuery($query);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('data')
        ->and($result['data'])->not->toBeEmpty();
});
