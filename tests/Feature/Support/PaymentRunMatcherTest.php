<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\UpdatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Transaction;
use FluxErp\Settings\AccountingSettings;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Support\Matching\PaymentRunMatcher;

function createNettedPosition(): array
{
    $invoice = createPaymentRunOrder(OrderTypeEnum::Order);
    $creditNote = createPaymentRunOrder(OrderTypeEnum::Order);

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
    [$paymentRun, $position, $invoice, $creditNote] = createNettedPosition();

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

test('a transaction matches a money transfer position only on its direction, amount and recipient', function (
    float $amount,
    bool $withReference,
    ?string $counterpartIban,
    bool $expectedMatch
): void {
    [, $position] = createNettedPosition();

    $transaction = Transaction::factory()->create([
        'amount' => $amount,
        'end_to_end_reference' => $withReference ? $position->end_to_end_id : null,
        'counterpart_iban' => $counterpartIban ?? $position->iban,
        'booking_date' => now(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($transaction))->toBe($expectedMatch)
        ->and(OrderTransaction::query()->where('transaction_id', $transaction->getKey())->exists())->toBe($expectedMatch);
})->with([
    'without reference on amount, iban and date' => [-800, false, null, true],
    'an unrelated transaction' => [-12.34, false, 'DE02500105170137075030', false],
    'the right reference but the wrong amount' => [-750, true, null, false],
    'an incoming payment under the reference' => [800, true, null, false],
    'an incoming payment from the recipient iban' => [800, false, null, false],
]);

test('a collective booking is split across all positions of the run', function (): void {
    [$paymentRun, $position] = createNettedPosition();
    $second = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-200.00',
        'iban' => 'DE02120300000000202051',
    ]);
    $other = createPaymentRunOrder(OrderTypeEnum::Order);
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
    [$paymentRun, $position] = createNettedPosition();

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

test('a collective booking leaves a netted position untouched', function (): void {
    $clearing = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    app(AccountingSettings::class)->clearing_ledger_account_id = $clearing->getKey();

    $payable = createPaymentRunOrder(OrderTypeEnum::Purchase, '-800.00');
    $invoice = createPaymentRunOrder(OrderTypeEnum::Purchase, '-500.00');
    $creditNote = createPaymentRunOrder(OrderTypeEnum::PurchaseRefund, '500.00');

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

test('an outgoing payment is never booked onto a direct debit run', function (): void {
    $order = createPaymentRunOrder(OrderTypeEnum::Order);

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
    [$paymentRun, $position, $invoice] = createNettedPosition();

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

test('a transfer refunding an overpaid sales invoice net of a supplier credit note settles both', function (): void {
    $invoice = createPaymentRunOrder(OrderTypeEnum::Order, '404.12');
    $creditNote = createPaymentRunOrder(OrderTypeEnum::PurchaseRefund, '200.00');
    $creditNote->update(['contact_id' => $invoice->contact_id]);

    OrderTransaction::query()->create([
        'order_id' => $invoice->getKey(),
        'transaction_id' => Transaction::factory()
            ->create(['amount' => 808.24, 'contact_bank_connection_id' => null])
            ->getKey(),
        'amount' => 808.24,
        'is_accepted' => true,
    ]);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -404.12],
                    ['order_id' => $creditNote->getKey(), 'amount' => 200],
                ],
            ],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
        'instructed_execution_date' => now()->format('Y-m-d'),
    ])->validate()->execute();

    $position = $run->positions()->sole();

    $transaction = Transaction::factory()->create([
        'amount' => -204.12,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);

    expect($position->amount)->toEqual('-204.12')
        ->and(app(PaymentRunMatcher::class)->match($transaction))->toBeTrue()
        ->and(OrderTransaction::query()
            ->where('transaction_id', $transaction->getKey())
            ->pluck('amount', 'order_id')
            ->map(fn ($amount) => bcround((string) $amount, 2))
            ->all())->toEqual([$invoice->getKey() => '-404.12', $creditNote->getKey() => '200.00'])
        ->and($invoice->fresh()->balance)->toEqual('0.00')
        ->and($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($creditNote->fresh()->balance)->toEqual('0.00')
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('a later payment of the same amount to the same recipient is not booked onto a reconciled position', function (): void {
    [, $position] = createNettedPosition();

    $first = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => $position->end_to_end_id,
        'counterpart_iban' => $position->iban,
        'booking_date' => now(),
    ]);
    $second = Transaction::factory()->create([
        'amount' => -800,
        'end_to_end_reference' => null,
        'counterpart_iban' => $position->iban,
        'booking_date' => now()->addDay(),
    ]);

    expect(app(PaymentRunMatcher::class)->match($first))->toBeTrue()
        ->and(app(PaymentRunMatcher::class)->match($second))->toBeFalse()
        ->and(OrderTransaction::query()->where('transaction_id', $second->getKey())->exists())->toBeFalse();
});
