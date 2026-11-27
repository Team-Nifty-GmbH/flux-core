<?php

use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\OrderTransaction\DeleteOrderTransaction;
use FluxErp\Actions\OrderTransaction\UpdateOrderTransaction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();

    $this->order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
    $this->transaction = Transaction::factory()->create();
});

test('create order transaction', function (): void {
    $ot = CreateOrderTransaction::make([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => 100.00,
    ])->validate()->execute();

    expect($ot)->toBeInstanceOf(OrderTransaction::class);
});

test('create order transaction derives the exchange rate from both amounts', function (): void {
    $ot = CreateOrderTransaction::make([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => -4.35,
        'order_currency_amount' => -5.00,
        'exchange_rate' => 0.87,
    ])->validate()->execute();

    expect(bccomp($ot->exchange_rate, '1.1494252873', 10))->toBe(0);
});

test('update order transaction derives the exchange rate when the amount changes', function (): void {
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => -4.35,
        'order_currency_amount' => -5.00,
        'exchange_rate' => 1.1494252874,
    ]);

    $updated = UpdateOrderTransaction::make([
        'pivot_id' => $ot->getKey(),
        'amount' => -4.00,
    ])->validate()->execute();

    expect(bccomp($updated->exchange_rate, '1.25', 10))->toBe(0);
});

test('update order transaction keeps the exchange rate when neither amount changes', function (): void {
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => -4.35,
        'order_currency_amount' => -5.00,
        'is_accepted' => false,
    ]);
    DB::table('order_transaction')->where('pivot_id', $ot->getKey())->update(['exchange_rate' => 1.15]);

    $updated = UpdateOrderTransaction::make([
        'pivot_id' => $ot->getKey(),
        'is_accepted' => true,
    ])->validate()->execute();

    expect($updated->is_accepted)->toBeTrue()
        ->and(bccomp($updated->exchange_rate, '1.15', 10))->toBe(0);
});

test('create order transaction drops a rate that has no order currency amount', function (): void {
    $ot = CreateOrderTransaction::make([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => -4.35,
        'exchange_rate' => 0.87,
    ])->validate()->execute();

    expect($ot->refresh()->exchange_rate)->toBeNull();
});

test('create order transaction drops the rate when the amount is zero', function (): void {
    $ot = CreateOrderTransaction::make([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => 0,
        'order_currency_amount' => -5.00,
        'exchange_rate' => 0.87,
    ])->validate()->execute();

    expect($ot->refresh()->exchange_rate)->toBeNull();
});

test('migration recalculates stored exchange rates from both amounts', function (): void {
    $inverted = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => -4.35,
        'order_currency_amount' => -5.00,
    ]);
    $withoutOrderAmount = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => 10,
    ]);
    $zeroAmount = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => 0,
        'order_currency_amount' => 5,
    ]);
    DB::table('order_transaction')
        ->whereIn('pivot_id', [$inverted->getKey(), $withoutOrderAmount->getKey(), $zeroAmount->getKey()])
        ->update(['exchange_rate' => 0.87]);

    (require __DIR__
        . '/../../../../database/migrations/2026_11_25_000001_recalculate_exchange_rate_on_order_transaction_table.php')
        ->up();

    expect(bccomp($inverted->refresh()->exchange_rate, '1.1494252873', 10))->toBe(0)
        ->and(bccomp($withoutOrderAmount->refresh()->exchange_rate, '0.87', 10))->toBe(0)
        ->and(bccomp($zeroAmount->refresh()->exchange_rate, '0.87', 10))->toBe(0);
});

test('create order transaction requires transaction_id order_id amount', function (): void {
    CreateOrderTransaction::assertValidationErrors([], ['transaction_id', 'order_id', 'amount']);
});

test('update order transaction', function (): void {
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
    ]);

    $updated = UpdateOrderTransaction::make([
        'pivot_id' => $ot->getKey(),
        'amount' => 200.00,
    ])->validate()->execute();

    expect($updated->amount)->toEqual(200.00);
});

test('delete order transaction', function (): void {
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
    ]);

    expect(DeleteOrderTransaction::make(['pivot_id' => $ot->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('delete order transaction of a deleted transaction', function (): void {
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'is_accepted' => true,
    ]);
    $this->transaction->delete();

    DeleteOrderTransaction::make(['pivot_id' => $ot->getKey()])->validate()->execute();

    expect(OrderTransaction::query()->whereKey($ot->getKey())->exists())->toBeFalse();
});

test('update order transaction of a deleted transaction', function (): void {
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
    ]);
    $this->transaction->delete();

    UpdateOrderTransaction::make(['pivot_id' => $ot->getKey(), 'amount' => 50.00])->validate()->execute();

    expect($ot->refresh()->amount)->toEqual(50.00);
});

test('deleting the assignment of a deleted transaction recalculates its balance', function (): void {
    $this->transaction->update(['amount' => 100, 'contact_bank_connection_id' => null]);
    $ot = OrderTransaction::factory()->create([
        'transaction_id' => $this->transaction->getKey(),
        'order_id' => $this->order->getKey(),
        'amount' => 100,
        'is_accepted' => true,
    ]);
    $this->transaction->delete();

    DeleteOrderTransaction::make(['pivot_id' => $ot->getKey()])->validate()->execute();

    expect(Transaction::withTrashed()->find($this->transaction->getKey())->balance)->toEqual(100);
});
