<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\DB;

function createOrderForPaymentRunPosition(object $test, array $attributes = []): Order
{
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    return Order::factory()->create(array_merge([
        'order_type_id' => OrderType::factory()->create(['is_active' => true])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $test->dbTenant->getKey(),
    ], $attributes));
}

test('a payment run has positions and a position has orders', function (): void {
    $paymentRun = PaymentRun::factory()->create();
    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'iban' => 'DE89370400440532013000',
        'amount' => '-1300.00',
    ]);

    expect($paymentRun->positions()->count())->toBe(1)
        ->and($position->paymentRun->getKey())->toBe($paymentRun->getKey())
        ->and(morph_alias(PaymentRunPosition::class))->toBe('payment_run_position');
});

test('the pivot points at its position', function (): void {
    $paymentRun = PaymentRun::factory()->create();
    $position = PaymentRunPosition::factory()->create(['payment_run_id' => $paymentRun->getKey()]);
    $order = createOrderForPaymentRunPosition($this);

    $position->orders()->attach($order->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-1000.00',
    ]);

    expect($position->orders()->count())->toBe(1)
        ->and($paymentRun->orders()->count())->toBe(1);
});

test('every existing pivot row gets its own position', function (): void {
    $paymentRun = PaymentRun::factory()->create();
    $order = createOrderForPaymentRunPosition($this, ['invoice_number' => 'RE-1']);

    DB::table('order_payment_run')->insert([
        'order_id' => $order->getKey(),
        'payment_run_id' => $paymentRun->getKey(),
        'payment_run_position_id' => null,
        'amount' => '-250.00',
    ]);

    (new BackfillPaymentRunPositions())->backfill();

    $pivot = DB::table('order_payment_run')->where('order_id', $order->getKey())->first();
    $position = PaymentRunPosition::query()->find($pivot->payment_run_position_id);

    expect($position)->not->toBeNull()
        ->and($position->amount)->toEqual('-250.00')
        ->and($position->end_to_end_id)->toBe('RE-1');
});

test('the backfill covers every page, not just the first', function (): void {
    $order = createOrderForPaymentRunPosition($this);

    foreach (range(1, 5) as $i) {
        DB::table('order_payment_run')->insert([
            'order_id' => $order->getKey(),
            'payment_run_id' => PaymentRun::factory()->create()->getKey(),
            'payment_run_position_id' => null,
            'amount' => "-{$i}00.00",
        ]);
    }

    (new BackfillPaymentRunPositions())->backfill(2);

    expect(DB::table('order_payment_run')->whereNull('payment_run_position_id')->count())->toBe(0)
        ->and(PaymentRunPosition::query()->count())->toBe(5);
});
