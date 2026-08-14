<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\UpdatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\PriceList;
use FluxErp\Models\Transaction;
use FluxErp\Settings\AccountingSettings;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Support\Matching\PaymentRunMatcher;

function createOrderForPaymentRunMatcher(
    object $testContext,
    OrderTypeEnum $orderTypeEnum = OrderTypeEnum::Order,
    ?string $grossPrice = null
): Order {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => $orderTypeEnum,
        'is_active' => true,
        'is_hidden' => false,
    ]);
    $paymentType = PaymentType::factory()->hasAttached($testContext->dbTenant, relationship: 'tenants')->create();

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $testContext->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $testContext->defaultLanguage->getKey(),
    ]);

    if ($grossPrice !== null) {
        $order->update(['total_gross_price' => $grossPrice, 'balance' => $grossPrice]);
    }

    return $order;
}

function createNettedPosition(object $testContext): array
{
    $invoice = createOrderForPaymentRunMatcher($testContext);
    $creditNote = createOrderForPaymentRunMatcher($testContext);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'account_holder' => 'Muster GmbH',
                'purpose' => 'RE-1, GS-1',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -1000],
                    ['order_id' => $creditNote->getKey(), 'amount' => 200],
                ],
            ],
        ],
    ])->validate()->execute();

    $run = UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
        'instructed_execution_date' => now()->format('Y-m-d'),
    ])->validate()->execute();

    $position = $run->positions()->first();

    return [$run, $position, $invoice, $creditNote];
}

test('a transaction is split across the orders of its position', function (): void {
    [$paymentRun, $position, $invoice, $creditNote] = createNettedPosition($this);

    $transaction = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeTrue()
        ->and(bccomp(bcround((string) $invoice->fresh()->orderTransactions()->first()->amount, 2), '-1000.00', 2))->toBe(0)
        ->and(bccomp(bcround((string) $creditNote->fresh()->orderTransactions()->first()->amount, 2), '200.00', 2))->toBe(0)
        ->and($invoice->fresh()->orderTransactions()->first()->is_accepted)->toBeTrue();
});

test('a transaction without reference matches on amount, iban and date', function (): void {
    [$paymentRun, $position] = createNettedPosition($this);

    $transaction = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => null,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeTrue();
});

test('a collective booking is split across all positions of the run', function (): void {
    [$paymentRun, $position] = createNettedPosition($this);
    $second = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-200.00',
        'iban' => 'DE02120300000000202051',
    ]);
    $other = createOrderForPaymentRunMatcher($this);
    $second->orders()->attach($other->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-200.00',
    ]);

    $paymentRun->update(['is_single_booking' => false]);

    $transaction = Transaction::factory()->create([
        'amount' => -1000,
        'end_to_end_reference' => null,
        'counterpart_iban' => null,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeTrue()
        ->and($other->fresh()->orderTransactions()->count())->toBe(1);
});

test('a position that is already assigned is left alone', function (): void {
    [$paymentRun, $position] = createNettedPosition($this);

    $transaction = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    app(PaymentRunMatcher::class)->match($transaction);
    $countAfterFirstRun = $position->orders()->first()->orderTransactions()->count();

    app(PaymentRunMatcher::class)->match($transaction);

    expect($position->orders()->first()->orderTransactions()->count())->toBe($countAfterFirstRun);
});

test('an unrelated transaction is not matched', function (): void {
    createNettedPosition($this);

    $transaction = Transaction::factory()->create([
        'amount' => -12.34,
        'end_to_end_reference' => null,
        'counterpart_iban' => 'DE02500105170137075030',
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeFalse();
});

test('a transaction under the right reference but the wrong amount is not assigned', function (): void {
    [$paymentRun, $position] = createNettedPosition($this);

    $transaction = Transaction::factory()->create([
        'amount' => -750,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeFalse()
        ->and(OrderTransaction::query()->where('transaction_id', $transaction->getKey())->exists())->toBeFalse();
});

test('a collective booking leaves a netted position untouched', function (): void {
    $clearing = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    app(AccountingSettings::class)->fill(['clearing_ledger_account_id' => $clearing->getKey()])->save();

    $payable = createOrderForPaymentRunMatcher($this, OrderTypeEnum::Purchase, '-800.00');
    $invoice = createOrderForPaymentRunMatcher($this, OrderTypeEnum::Purchase, '-500.00');
    $creditNote = createOrderForPaymentRunMatcher($this, OrderTypeEnum::PurchaseRefund, '500.00');

    Contact::query()
        ->whereKey([$payable->contact_id, $invoice->contact_id, $creditNote->contact_id])
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $payable->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $payable->getKey(), 'amount' => -800],
                ],
            ],
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE02120300000000202051',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -500],
                    ['order_id' => $creditNote->getKey(), 'amount' => 500],
                ],
            ],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
        'instructed_execution_date' => now()->format('Y-m-d'),
    ])->validate()->execute();

    $run->update(['is_single_booking' => false]);

    $transaction = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => null,
        'counterpart_iban' => null,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeTrue()
        ->and($payable->fresh()->orderTransactions()->count())->toBe(1)
        ->and($invoice->fresh()->orderTransactions()->count())->toBe(0)
        ->and($creditNote->fresh()->orderTransactions()->count())->toBe(0)
        ->and($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('an incoming payment is never booked onto a money transfer run', function (): void {
    [, $position] = createNettedPosition($this);

    $byReference = Transaction::factory()->create([
        'amount' => 800,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    $byIban = Transaction::factory()->create([
        'amount' => 800,
        'end_to_end_reference' => null,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($byReference))->toBeFalse()
        ->and(app(PaymentRunMatcher::class)->match($byIban))->toBeFalse()
        ->and(OrderTransaction::query()->whereIn(
            'transaction_id',
            [$byReference->getKey(), $byIban->getKey()]
        )->exists())->toBeFalse();
});

test('an outgoing payment is never booked onto a direct debit run', function (): void {
    $order = createOrderForPaymentRunMatcher($this);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'direct_debit',
        'positions' => [
            [
                'contact_id' => $order->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $order->getKey(), 'amount' => 800],
                ],
            ],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
        'instructed_execution_date' => now()->format('Y-m-d'),
    ])->validate()->execute();

    $transaction = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => $run->positions()->first()->end_to_end_id,
        'counterpart_iban' => 'DE89370400440532013000',
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeFalse()
        ->and(OrderTransaction::query()->where('transaction_id', $transaction->getKey())->exists())->toBeFalse();
});

test('a position with a pre-existing unrelated transaction stays matchable', function (): void {
    [$paymentRun, $position, $invoice] = createNettedPosition($this);

    $olderTransaction = Transaction::factory()->create([
        'amount' => -1,
        'booking_date' => now()->subMonth(),
    ]);

    OrderTransaction::query()->create([
        'order_id' => $invoice->getKey(),
        'transaction_id' => $olderTransaction->getKey(),
        'amount' => -1,
        'is_accepted' => true,
    ]);

    $transaction = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBeTrue();
});
