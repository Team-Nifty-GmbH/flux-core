<?php

use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    app('translator')->addLines(['*.Company' => 'Firma'], app()->getLocale());

    $this->migration = require __DIR__
        . '/../../../database/migrations/2026_08_03_000002_normalize_salutation_in_order_address_snapshots.php';
});

test('the migration turns a translated label into the enum value', function (): void {
    DB::table('orders')->where('id', $this->order->getKey())->update([
        'address_invoice' => json_encode(['salutation' => 'Firma', 'company' => 'Musterfirma GmbH']),
        'address_delivery' => json_encode(['salutation' => 'Firma']),
    ]);

    $this->migration->up();

    $order = $this->order->refresh();

    expect(data_get($order->address_invoice, 'salutation'))->toBe('company')
        ->and(data_get($order->address_delivery, 'salutation'))->toBe('company')
        ->and(data_get($order->address_invoice, 'company'))->toBe('Musterfirma GmbH');
});

test('the migration leaves an enum value and an unknown salutation alone', function (): void {
    DB::table('orders')->where('id', $this->order->getKey())->update([
        'address_invoice' => json_encode(['salutation' => 'mr']),
        'address_delivery' => json_encode(['salutation' => 'Kapitaen']),
    ]);

    $this->migration->up();

    $order = $this->order->refresh();

    expect(data_get($order->address_invoice, 'salutation'))->toBe('mr')
        ->and(data_get($order->address_delivery, 'salutation'))->toBe('Kapitaen');
});

test('the migration leaves a snapshot without a salutation untouched', function (): void {
    DB::table('orders')->where('id', $this->order->getKey())->update([
        'address_invoice' => json_encode(['company' => 'Musterfirma GmbH']),
        'address_delivery' => null,
    ]);

    $this->migration->up();

    $order = $this->order->refresh();

    expect($order->address_invoice)->toBe(['company' => 'Musterfirma GmbH'])
        ->and($order->address_delivery)->toBeNull();
});

test('updating an order rejects a translated salutation in the invoice snapshot', function (): void {
    UpdateOrder::make([
        'id' => $this->order->getKey(),
        'address_invoice' => ['salutation' => 'Firma'],
    ])->validate();
})->throws(ValidationException::class);

test('updating an order rejects a translated salutation in the delivery snapshot', function (): void {
    UpdateOrder::make([
        'id' => $this->order->getKey(),
        'address_delivery' => ['salutation' => 'Firma'],
    ])->validate();
})->throws(ValidationException::class);

test('updating an order accepts the enum value in both snapshots', function (): void {
    UpdateOrder::make([
        'id' => $this->order->getKey(),
        'address_invoice' => ['salutation' => 'company'],
        'address_delivery' => ['salutation' => 'company'],
    ])->validate()->execute();

    $order = $this->order->refresh();

    expect(data_get($order->address_invoice, 'salutation'))->toBe('company')
        ->and(data_get($order->address_delivery, 'salutation'))->toBe('company');
});
