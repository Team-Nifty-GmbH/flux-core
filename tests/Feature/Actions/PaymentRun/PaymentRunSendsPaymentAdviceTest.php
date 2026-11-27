<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\UpdatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\SendPaymentAdviceJob;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Settings\AccountingSettings;
use FluxErp\View\Printing\PaymentRun\PaymentAdvice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

function createOrderWithContact(string $invoiceNumber, ?string $email): Order
{
    $order = createPaymentRunOrder(OrderTypeEnum::Purchase, '-100.00', ['invoice_number' => $invoiceNumber]);
    $order->addressInvoice->update(['email_primary' => $email, 'is_invoice_address' => true]);
    $order->contact->update(['invoice_address_id' => $order->address_invoice_id]);

    return $order;
}

test('moving a run to pending sends no payment advice', function (bool $autoSend, string $paymentRunType, float $amount): void {
    Queue::fake();

    app(AccountingSettings::class)->auto_send_payment_advice = $autoSend;

    $order = createOrderWithContact('RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => $paymentRunType,
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => $amount],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    Queue::assertNotPushed(SendPaymentAdviceJob::class);
})->with([
    'with the setting off' => [false, 'money_transfer', -100.00],
    'for a direct debit run even with the setting on' => [true, 'direct_debit', 100.00],
]);

test('with the setting on, it sends one advice per position and reports a position without email as unsent', function (): void {
    config(['queue.default' => 'sync']);
    Mail::fake();

    app(AccountingSettings::class)->auto_send_payment_advice = true;

    $orderWithEmail = createOrderWithContact('RE-1', 'supplier@example.com');
    $orderWithoutEmail = createOrderWithContact('RE-2', null);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $orderWithEmail->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => [
                    ['order_id' => $orderWithEmail->getKey(), 'amount' => -100.00],
                ],
            ],
            [
                'contact_id' => $orderWithoutEmail->contact_id,
                'iban' => 'DE02120300000000202051',
                'orders' => [
                    ['order_id' => $orderWithoutEmail->getKey(), 'amount' => -100.00],
                ],
            ],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    $positionWithoutEmail = $run->positions()->where('contact_id', $orderWithoutEmail->contact_id)->sole();

    Mail::assertSent(GenericMail::class, 1);

    $logged = Activity::query()
        ->where('subject_type', morph_alias(PaymentRunPosition::class))
        ->where('subject_id', $positionWithoutEmail->getKey())
        ->where('event', 'payment_advice_send_failed')
        ->exists();

    expect($logged)->toBeTrue();
});

test('a position netted to zero is skipped for the payment advice', function (): void {
    Queue::fake();

    app(AccountingSettings::class)->auto_send_payment_advice = true;

    $invoice = createOrderWithContact('RE-1', 'supplier@example.com');
    $creditNote = createOrderWithContact('GS-1', 'supplier@example.com');

    $run = PaymentRun::factory()->create(['state' => 'open']);
    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $run->getKey(),
        'contact_id' => $invoice->contact_id,
        'amount' => 0,
    ]);
    $position->orders()->attach([
        $invoice->getKey() => ['payment_run_id' => $run->getKey(), 'amount' => -500],
        $creditNote->getKey() => ['payment_run_id' => $run->getKey(), 'amount' => 500],
    ]);

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    Queue::assertNotPushed(SendPaymentAdviceJob::class);
});

test('a second transition into pending does not resend the payment advice', function (): void {
    config(['queue.default' => 'sync']);
    Mail::fake();

    app(AccountingSettings::class)->auto_send_payment_advice = true;

    $order = createOrderWithContact('RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make(['id' => $run->getKey(), 'state' => 'pending'])->validate()->execute();
    UpdatePaymentRun::make(['id' => $run->getKey(), 'state' => 'not_successful'])->validate()->execute();
    UpdatePaymentRun::make(['id' => $run->getKey(), 'state' => 'pending'])->validate()->execute();

    Mail::assertSentTimes(GenericMail::class, 1);
});

test('a failed send leaves no marker and a retry tries again', function (): void {
    $order = createOrderWithContact('RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    $position = $run->positions()->sole();

    $pendingMail = Mockery::mock();
    $pendingMail->shouldReceive('to', 'cc', 'bcc')->andReturnSelf();
    $pendingMail->shouldReceive('send')
        ->twice()
        ->andThrow(new RuntimeException('SMTP connection refused'));
    Mail::shouldReceive('mailer')->andReturn($pendingMail);

    (new SendPaymentAdviceJob($position->getKey()))->handle();

    expect($position->fresh()->getMedia(PaymentAdvice::MEDIA_COLLECTION))->toBeEmpty();

    (new SendPaymentAdviceJob($position->getKey()))->handle();

    expect($position->fresh()->getMedia(PaymentAdvice::MEDIA_COLLECTION))->toBeEmpty();
});

test('a customized payment advice with its own media collection is not sent twice', function (): void {
    config(['queue.default' => 'sync']);
    Mail::fake();

    $customAdvice = new class(new PaymentRunPosition()) extends PaymentAdvice
    {
        public const string MEDIA_COLLECTION = 'custom-payment-advice';
    };
    app()->bind(PaymentAdvice::class, $customAdvice::class);

    $order = createOrderWithContact('RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    $position = $run->positions()->sole();

    (new SendPaymentAdviceJob($position->getKey()))->handle();
    (new SendPaymentAdviceJob($position->getKey()))->handle();

    Mail::assertSentTimes(GenericMail::class, 1);
    expect($position->fresh()->getMedia('custom-payment-advice'))->toHaveCount(1);
});
