<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->priceList = PriceList::factory()->create();
    $this->currency = Currency::factory()->create();

    $this->order = Order::factory()->create([
        'order_type_id' => $this->orderType->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => false,
    ]);

    // Legacy rows carry the snapshot as stored, so bypass the model to seed them.
    $this->seedSnapshot = function (array $address): Order {
        Order::query()->whereKey($this->order->getKey())->update([
            'address_invoice' => json_encode($address),
            'address_delivery' => json_encode($address),
        ]);

        $order = $this->order->fresh();
        $order->save();

        return $order->refresh();
    };

    // JSON translation lines live under the "*" namespace and group.
    app('translator')->addLines(['*.company' => 'Firma'], app()->getLocale());
});

test('a translated salutation label in the snapshot is normalized to the enum value', function (): void {
    $order = ($this->seedSnapshot)(['salutation' => 'Firma', 'company' => 'Musterfirma GmbH']);

    expect(data_get($order->address_invoice, 'salutation'))->toBe('company')
        ->and(data_get($order->address_delivery, 'salutation'))->toBe('company')
        ->and(data_get($order->address_invoice, 'company'))->toBe('Musterfirma GmbH');
});

test('an enum value in the snapshot is left alone', function (): void {
    $order = ($this->seedSnapshot)(['salutation' => 'mr']);

    expect(data_get($order->address_invoice, 'salutation'))->toBe('mr');
});

test('an unknown salutation in the snapshot is left alone', function (): void {
    $order = ($this->seedSnapshot)(['salutation' => 'Kapitaen']);

    expect(data_get($order->address_invoice, 'salutation'))->toBe('Kapitaen');
});

test('a snapshot without a salutation stays untouched', function (): void {
    $order = ($this->seedSnapshot)(['company' => 'Musterfirma GmbH']);

    expect($order->address_invoice)->toBe(['company' => 'Musterfirma GmbH']);
});

test('assigning a label to an existing order normalizes it on save', function (): void {
    $this->order->address_invoice = ['salutation' => 'Firma'];
    $this->order->save();

    expect(data_get($this->order->refresh()->address_invoice, 'salutation'))->toBe('company');
});
