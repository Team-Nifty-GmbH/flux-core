<?php

use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Warehouse;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;

beforeEach(function (): void {
    Warehouse::factory()->create(['is_default' => true]);

    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->priceList = PriceList::factory()->create();
    $this->currency = Currency::factory()->create();

    $this->debitAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
    ]);
    $this->creditAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
    ]);

    $this->createOrder = function (OrderTypeEnum $orderTypeEnum, float $totalGrossPrice): Order {
        $orderType = OrderType::factory()->create([
            'order_type_enum' => $orderTypeEnum,
            'is_active' => true,
        ]);

        return Order::factory()->create([
            'order_type_id' => $orderType->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'contact_id' => $this->contact->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'tenant_id' => $this->dbTenant->getKey(),
            'currency_id' => $this->currency->getKey(),
            'language_id' => $this->defaultLanguage->getKey(),
            'total_gross_price' => $totalGrossPrice,
            'balance' => $totalGrossPrice,
            'is_locked' => false,
        ]);
    };

    $this->book = fn (Order $order, float $amount) => CreateLedgerBooking::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
        'source_type' => $order->getMorphClass(),
        'source_id' => $order->getKey(),
        'amount' => $amount,
        'booking_date' => '2026-07-01',
        'booking_text' => 'Umbuchung Kreditor an Darlehen',
    ])
        ->validate()
        ->execute();
});

test('a ledger booking settles the purchase order it points at', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -52320);

    ($this->book)($order, 52320);

    $order->refresh();

    expect((float) $order->balance)->toBe(0.0)
        ->and($order->payment_state)->toBeInstanceOf(Paid::class);
});

test('a ledger booking settles the sales order it points at', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Order, 1190);

    ($this->book)($order, 1190);

    $order->refresh();

    expect((float) $order->balance)->toBe(0.0)
        ->and($order->payment_state)->toBeInstanceOf(Paid::class);
});

test('a partial ledger booking leaves the remainder open', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -52320);

    ($this->book)($order, 12320);

    $order->refresh();

    expect((float) $order->balance)->toBe(-40000.0)
        ->and($order->payment_state->getMorphClass())->not->toBe(Paid::class);
});

test('deleting the ledger booking reopens the order', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -52320);

    $booking = ($this->book)($order, 52320);

    DeleteLedgerBooking::make(['id' => $booking->getKey()])
        ->validate()
        ->execute();

    $order->refresh();

    expect((float) $order->balance)->toBe(-52320.0)
        ->and($order->payment_state)->toBeInstanceOf(Open::class);
});

test('a ledger booking without a source leaves orders untouched', function (): void {
    $order = ($this->createOrder)(OrderTypeEnum::Purchase, -52320);

    CreateLedgerBooking::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
        'amount' => 52320,
        'booking_date' => '2026-07-01',
    ])
        ->validate()
        ->execute();

    expect((float) $order->refresh()->balance)->toBe(-52320.0);
});
