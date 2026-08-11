<?php

use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $this->currency = Currency::factory()->create(['is_default' => true]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create(['requires_manual_transfer' => false]);
    $this->priceList = PriceList::factory()->create();
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);

    $this->createPurchaseInvoice = function (float $total = 119): PurchaseInvoice {
        $purchaseInvoice = PurchaseInvoice::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'contact_id' => $this->contact->getKey(),
            'currency_id' => $this->currency->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'order_type_id' => $this->orderType->getKey(),
            'invoice_number' => 'RE-' . fake()->unique()->numberBetween(1000, 9999),
            'invoice_date' => '2026-08-01',
            'payment_target_date' => '2026-08-31',
            'payment_discount_target_date' => null,
            'payment_discount_percent' => null,
            'total_gross_price' => $total,
            'is_net' => false,
            'order_id' => null,
        ]);

        $purchaseInvoice
            ->addMedia(UploadedFile::fake()->image('invoice.jpg'))
            ->toMediaCollection('purchase_invoice');

        $purchaseInvoice->purchaseInvoicePositions()->create([
            'name' => 'Monthly plan',
            'amount' => 1,
            'unit_price' => $total,
            'total_price' => $total,
            'vat_rate_id' => $this->vatRate->getKey(),
        ]);

        return $purchaseInvoice->refresh();
    };

    $this->createOrder = function (float $total = 119, array $attributes = []): Order {
        $order = Order::factory()->create(array_merge([
            'order_type_id' => $this->orderType->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'contact_id' => $this->contact->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'currency_id' => $this->currency->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'invoice_number' => null,
            'total_gross_price' => $total,
            'is_locked' => false,
        ], $attributes));

        OrderPosition::factory()->create([
            'order_id' => $order->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'name' => 'Planned rate',
            'amount' => 1,
            'unit_net_price' => 100,
            'is_free_text' => false,
            'is_alternative' => false,
        ]);

        return $order->refresh();
    };
});

test('finishing without a chosen order creates a new one', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();

    $order = CreateOrderFromPurchaseInvoice::make(['id' => $purchaseInvoice->getKey()])
        ->validate()
        ->execute();

    expect($order->invoice_number)->toBe($purchaseInvoice->invoice_number)
        ->and($purchaseInvoice->refresh()->order_id)->toBe($order->getKey());
});

test('finishing onto a chosen order reuses it instead of creating another', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();
    $existing = ($this->createOrder)();
    $countBefore = Order::query()->count();

    $order = CreateOrderFromPurchaseInvoice::make([
        'id' => $purchaseInvoice->getKey(),
        'order_id' => $existing->getKey(),
    ])
        ->validate()
        ->execute();

    expect($order->getKey())->toBe($existing->getKey())
        ->and(Order::query()->count())->toBe($countBefore)
        ->and($order->invoice_number)->toBe($purchaseInvoice->invoice_number)
        ->and($purchaseInvoice->refresh()->order_id)->toBe($existing->getKey());
});

test('the positions of the chosen order are replaced by the invoice positions', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();
    $existing = ($this->createOrder)();

    $order = CreateOrderFromPurchaseInvoice::make([
        'id' => $purchaseInvoice->getKey(),
        'order_id' => $existing->getKey(),
    ])
        ->validate()
        ->execute();

    $positions = $order->orderPositions()->pluck('name');

    expect($order->getKey())->toBe($existing->getKey())
        ->and($positions)->toHaveCount(1)
        ->and($positions->first())->toBe('Monthly plan');
});

test('the invoice document moves to the chosen order', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();
    $existing = ($this->createOrder)();

    $order = CreateOrderFromPurchaseInvoice::make([
        'id' => $purchaseInvoice->getKey(),
        'order_id' => $existing->getKey(),
    ])
        ->validate()
        ->execute();

    expect($order->getKey())->toBe($existing->getKey())
        ->and($order->getMedia('invoice'))->toHaveCount(1);
});

test('an already invoiced order cannot be chosen', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();
    $existing = ($this->createOrder)(119, ['invoice_number' => 'RE-EXISTING']);

    expect(fn () => CreateOrderFromPurchaseInvoice::make([
        'id' => $purchaseInvoice->getKey(),
        'order_id' => $existing->getKey(),
    ])->validate()->execute())->toThrow(ValidationException::class);
});

test('a locked order cannot be chosen', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();
    $existing = ($this->createOrder)(119, ['is_locked' => true]);

    expect(fn () => CreateOrderFromPurchaseInvoice::make([
        'id' => $purchaseInvoice->getKey(),
        'order_id' => $existing->getKey(),
    ])->validate()->execute())->toThrow(ValidationException::class);
});

test('an order of another contact cannot be chosen', function (): void {
    $purchaseInvoice = ($this->createPurchaseInvoice)();
    $existing = ($this->createOrder)(119, [
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);

    expect(fn () => CreateOrderFromPurchaseInvoice::make([
        'id' => $purchaseInvoice->getKey(),
        'order_id' => $existing->getKey(),
    ])->validate()->execute())->toThrow(ValidationException::class);
});
