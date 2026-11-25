<?php

use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->migration = require __DIR__
        . '/../../../database/migrations/2026_08_13_000003_backfill_payment_run_positions.php';
});

test('every existing pivot row gets its own position', function (): void {
    $paymentRun = PaymentRun::factory()->create();
    $order = createPaymentRunOrder(attributes: ['invoice_number' => 'RE-1']);

    DB::table('order_payment_run')->insert([
        'order_id' => $order->getKey(),
        'payment_run_id' => $paymentRun->getKey(),
        'payment_run_position_id' => null,
        'amount' => '-250.00',
    ]);

    $this->migration->backfill();

    $pivot = DB::table('order_payment_run')->where('order_id', $order->getKey())->first();
    $position = PaymentRunPosition::query()->find($pivot->payment_run_position_id);

    expect($position)->not->toBeNull()
        ->and($position->amount)->toEqual('-250.00')
        ->and($position->end_to_end_id)->toBe('PR' . $paymentRun->getKey() . '-' . $position->getKey());
});

test('orders sharing an invoice number in one old run each get their own position', function (): void {
    $paymentRun = PaymentRun::factory()->create();

    foreach (range(1, 2) as $i) {
        DB::table('order_payment_run')->insert([
            'order_id' => createPaymentRunOrder(attributes: ['invoice_number' => 'SUPPLIER-1'])->getKey(),
            'payment_run_id' => $paymentRun->getKey(),
            'payment_run_position_id' => null,
            'amount' => '-100.00',
        ]);
    }

    $this->migration->backfill();

    expect(DB::table('order_payment_run')->whereNull('payment_run_position_id')->count())->toBe(0)
        ->and($paymentRun->positions()->pluck('purpose')->all())->toBe(['SUPPLIER-1', 'SUPPLIER-1']);
});

test('the backfill covers every page, not just the first', function (): void {
    $order = createPaymentRunOrder();

    foreach (range(1, 5) as $i) {
        DB::table('order_payment_run')->insert([
            'order_id' => $order->getKey(),
            'payment_run_id' => PaymentRun::factory()->create()->getKey(),
            'payment_run_position_id' => null,
            'amount' => "-{$i}00.00",
        ]);
    }

    $this->migration->backfill(2);

    expect(DB::table('order_payment_run')->whereNull('payment_run_position_id')->count())->toBe(0)
        ->and(PaymentRunPosition::query()->count())->toBe(5);
});
