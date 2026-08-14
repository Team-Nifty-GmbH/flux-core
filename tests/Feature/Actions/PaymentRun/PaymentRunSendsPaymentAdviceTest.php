<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\UpdatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\SendPaymentAdviceJob;
use FluxErp\Mail\GenericMail;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Settings\AccountingSettings;
use FluxErp\View\Printing\PaymentRun\PaymentAdvice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

function createOrderWithContact(object $testContext, string $invoiceNumber, ?string $email): array
{
    $bankConnection = BankConnection::factory()->create();
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => $email,
        'is_invoice_address' => true,
    ]);
    $contact->update(['invoice_address_id' => $address->getKey()]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
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
        'invoice_number' => $invoiceNumber,
    ]);
    $order->update(['total_gross_price' => '-100.00', 'balance' => '-100.00']);

    return [$bankConnection, $order];
}

test('with the setting off, moving a run to pending sends nothing', function (): void {
    Queue::fake();

    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => false,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => null,
    ]);

    [$bankConnection, $order] = createOrderWithContact($this, 'RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => -100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    Queue::assertNotPushed(SendPaymentAdviceJob::class);
});

test('with the setting on, it sends one advice per position and reports a position without email as unsent', function (): void {
    config(['queue.default' => 'sync']);
    Mail::fake();

    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => true,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => null,
    ]);

    [$bankConnection, $orderWithEmail] = createOrderWithContact($this, 'RE-1', 'supplier@example.com');
    [, $orderWithoutEmail] = createOrderWithContact($this, 'RE-2', null);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
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

test('a direct debit run sends nothing even with the setting on', function (): void {
    Queue::fake();

    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => true,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => null,
    ]);

    [$bankConnection, $order] = createOrderWithContact($this, 'RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'direct_debit',
        'iban' => 'DE89370400440532013000',
        'orders' => [
            ['order_id' => $order->getKey(), 'amount' => 100.00],
        ],
    ])->validate()->execute();

    UpdatePaymentRun::make([
        'id' => $run->getKey(),
        'state' => 'pending',
    ])->validate()->execute();

    Queue::assertNotPushed(SendPaymentAdviceJob::class);
});

test('a position netted to zero is skipped for the payment advice', function (): void {
    Queue::fake();

    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => true,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => null,
    ]);

    $clearing = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => false,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => $clearing->getKey(),
    ]);

    [$bankConnection, $invoice] = createOrderWithContact($this, 'RE-1', 'supplier@example.com');
    [, $creditNote] = createOrderWithContact($this, 'GS-1', 'supplier@example.com');

    Contact::query()
        ->whereKey([$invoice->contact_id, $creditNote->contact_id])
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
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
    ])->validate()->execute();

    Queue::assertNotPushed(SendPaymentAdviceJob::class);
});

test('a second transition into pending does not resend the payment advice', function (): void {
    config(['queue.default' => 'sync']);
    Mail::fake();

    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => true,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => null,
    ]);

    [$bankConnection, $order] = createOrderWithContact($this, 'RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
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
    [$bankConnection, $order] = createOrderWithContact($this, 'RE-1', 'supplier@example.com');

    $run = CreatePaymentRun::make([
        'bank_connection_id' => $bankConnection->getKey(),
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
