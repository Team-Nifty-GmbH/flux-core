<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\PaymentRunList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\OrderPaymentRun;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Settings\AccountingSettings;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use Livewire\Livewire;

function createOrderForPaymentRunList(
    ?string $invoiceNumber = null,
    OrderTypeEnum $orderTypeEnum = OrderTypeEnum::Purchase,
    ?string $grossPrice = null
): Order {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => $orderTypeEnum,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'invoice_number' => $invoiceNumber,
    ]);

    if ($grossPrice !== null) {
        $order->update(['total_gross_price' => $grossPrice, 'balance' => $grossPrice]);
    }

    return $order;
}

test('renders successfully', function (): void {
    Livewire::test(PaymentRunList::class)
        ->assertOk();
});

test('edit loads positions with their orders into payment run form', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['is_active' => true, 'is_hidden' => false]);
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ]);

    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'account_holder' => 'Muster GmbH',
        'amount' => '100.00',
    ]);
    $position->orders()->attach($order->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '100.00',
    ]);

    $component = Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun)
        ->assertOpensModal('execute-payment-run');

    $positions = $component->get('paymentRunForm.positions');

    expect($positions)->toBeArray()
        ->and($positions)->not->toBeEmpty()
        ->and($positions[0]['account_holder'])->toBe('Muster GmbH')
        ->and($positions[0]['orders'][0]['id'])->toBe($order->getKey());
});

test('removing an order recalculates its position', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-1500.00',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['is_active' => true, 'is_hidden' => false]);
    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ];

    $first = Order::factory()->create($orderData);
    $second = Order::factory()->create($orderData);

    $position->orders()->attach([
        $first->getKey() => ['payment_run_id' => $paymentRun->getKey(), 'amount' => '-1000.00'],
        $second->getKey() => ['payment_run_id' => $paymentRun->getKey(), 'amount' => '-500.00'],
    ]);

    Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun)
        ->call('removeOrder', $first->getKey());

    expect($position->fresh()->amount)->toEqual('-500.00');
});

test('removing the last order of a position deletes it', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-1000.00',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['is_active' => true, 'is_hidden' => false]);
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ]);

    $position->orders()->attach($order->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-1000.00',
    ]);

    Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun)
        ->call('removeOrder', $order->getKey());

    expect(PaymentRunPosition::query()->whereKey($position->getKey())->exists())->toBeFalse()
        ->and(PaymentRun::query()->whereKey($paymentRun->getKey())->exists())->toBeFalse();
});

test('emptying one position deletes only that position and keeps the run and its other position intact', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $emptiedPosition = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-1000.00',
    ]);
    $survivingPosition = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-300.00',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['is_active' => true, 'is_hidden' => false]);
    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ];

    $orderToRemove = Order::factory()->create($orderData);
    $survivingOrder = Order::factory()->create($orderData);

    $emptiedPosition->orders()->attach($orderToRemove->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-1000.00',
    ]);
    $survivingPosition->orders()->attach($survivingOrder->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-300.00',
    ]);

    Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun)
        ->call('removeOrder', $orderToRemove->getKey());

    expect(PaymentRunPosition::query()->whereKey($emptiedPosition->getKey())->exists())->toBeFalse()
        ->and(PaymentRun::query()->whereKey($paymentRun->getKey())->exists())->toBeTrue()
        ->and($survivingPosition->fresh()->amount)->toEqual('-300.00')
        ->and($survivingPosition->fresh()->orders()->count())->toBe(1);
});

test('edit is renderless so it does not trigger empty re-render', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    // DataTable.dehydrate() clears $this->data after each render.
    // If edit() triggers a re-render, render() sees initialized=true,
    // skips loadData(), and renders with empty data → table goes blank.
    // edit() must be #[Renderless] to avoid this.
    $component = Livewire::test(PaymentRunList::class)
        ->call('edit', $paymentRun);

    $method = new ReflectionMethod(PaymentRunList::class, 'edit');
    $attributes = $method->getAttributes(\Livewire\Attributes\Renderless::class);

    expect($attributes)->not->toBeEmpty('edit() must have #[Renderless] to prevent clearing table data');
});

test('the list carries the total amount of a payment run', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();

    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ];

    $paymentRun->orders()->attach(Order::factory()->create($orderData)->getKey(), ['amount' => '100.00']);
    $paymentRun->orders()->attach(Order::factory()->create($orderData)->getKey(), ['amount' => '250.50']);

    expect((float) $paymentRun->refresh()->total_amount)->toBe(350.50);
});

test('removing an order updates the stored total', function (): void {
    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create();

    $orderData = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => Tenant::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => $orderType->getKey(),
    ];

    $stays = Order::factory()->create($orderData);
    $goes = Order::factory()->create($orderData);

    $paymentRun->orders()->attach($stays->getKey(), ['amount' => '100.00']);
    $paymentRun->orders()->attach($goes->getKey(), ['amount' => '250.50']);

    $paymentRun->orders()->detach($goes->getKey());

    expect((float) $paymentRun->refresh()->total_amount)->toBe(100.0);
});

test('the total amount is enabled by default', function (): void {
    expect((new PaymentRunList())->enabledCols)->toContain('total_amount');
});

test('a position left pointing against a money transfer is dropped entirely', function (): void {
    $invoice = createOrderForPaymentRunList('RE-1');
    $creditNote = createOrderForPaymentRunList('GS-1');
    $untouched = createOrderForPaymentRunList('RE-2');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'purpose' => 'RE-1, GS-1',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -1000],
                    ['order_id' => $creditNote->getKey(), 'amount' => 200],
                ],
            ],
            [
                'contact_id' => $untouched->contact_id,
                'iban' => 'DE02120300000000202051',
                'purpose' => 'RE-2',
                'orders' => [
                    ['order_id' => $untouched->getKey(), 'amount' => -300],
                ],
            ],
        ],
    ])->validate()->execute();

    $droppedPosition = $run->positions()->where('purpose', 'RE-1, GS-1')->first();

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $invoice->getKey());

    expect(PaymentRunPosition::query()->whereKey($droppedPosition->getKey())->exists())->toBeFalse()
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Open::class)
        ->and(PaymentRun::query()->whereKey($run->getKey())->exists())->toBeTrue()
        ->and($run->fresh()->positions()->count())->toBe(1)
        ->and($run->fresh()->total_amount)->toEqual('-300.00');
});

test('a position left at zero is dropped entirely', function (): void {
    $first = createOrderForPaymentRunList('RE-1');
    $second = createOrderForPaymentRunList('RE-2');
    $creditNote = createOrderForPaymentRunList('GS-1');
    $untouched = createOrderForPaymentRunList('RE-3');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $first->contact_id,
                'iban' => 'DE89370400440532013000',
                'purpose' => 'RE-1, RE-2, GS-1',
                'orders' => [
                    ['order_id' => $first->getKey(), 'amount' => -1000],
                    ['order_id' => $second->getKey(), 'amount' => -200],
                    ['order_id' => $creditNote->getKey(), 'amount' => 200],
                ],
            ],
            [
                'contact_id' => $untouched->contact_id,
                'iban' => 'DE02120300000000202051',
                'purpose' => 'RE-3',
                'orders' => [
                    ['order_id' => $untouched->getKey(), 'amount' => -300],
                ],
            ],
        ],
    ])->validate()->execute();

    $droppedPosition = $run->positions()->where('purpose', 'RE-1, RE-2, GS-1')->first();

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $first->getKey());

    expect(PaymentRunPosition::query()->whereKey($droppedPosition->getKey())->exists())->toBeFalse()
        ->and($second->fresh()->payment_state)->toBeInstanceOf(Open::class)
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Open::class)
        ->and($run->fresh()->total_amount)->toEqual('-300.00');
});

test('a position left pointing against a direct debit is dropped entirely', function (): void {
    $invoice = createOrderForPaymentRunList('RE-1');
    $creditNote = createOrderForPaymentRunList('GS-1');
    $untouched = createOrderForPaymentRunList('RE-2');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'direct_debit',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'purpose' => 'RE-1, GS-1',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => 1000],
                    ['order_id' => $creditNote->getKey(), 'amount' => -200],
                ],
            ],
            [
                'contact_id' => $untouched->contact_id,
                'iban' => 'DE02120300000000202051',
                'purpose' => 'RE-2',
                'orders' => [
                    ['order_id' => $untouched->getKey(), 'amount' => 300],
                ],
            ],
        ],
    ])->validate()->execute();

    $droppedPosition = $run->positions()->where('purpose', 'RE-1, GS-1')->first();

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $invoice->getKey());

    expect(PaymentRunPosition::query()->whereKey($droppedPosition->getKey())->exists())->toBeFalse()
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Open::class)
        ->and($run->fresh()->total_amount)->toEqual('300.00');
});

test('removing an order rebuilds the purpose but keeps the end to end id', function (): void {
    $first = createOrderForPaymentRunList('RE-1');
    $second = createOrderForPaymentRunList('RE-2');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $first->contact_id,
                'iban' => 'DE89370400440532013000',
                'purpose' => 'RE-1, RE-2',
                'orders' => [
                    ['order_id' => $first->getKey(), 'amount' => -1000],
                    ['order_id' => $second->getKey(), 'amount' => -500],
                ],
            ],
        ],
    ])->validate()->execute();

    $position = $run->positions()->first();
    $endToEndId = $position->end_to_end_id;

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $first->getKey());

    expect($position->fresh()->purpose)->toBe('RE-2')
        ->and($position->fresh()->end_to_end_id)->toBe($endToEndId)
        ->and($position->fresh()->amount)->toEqual('-500.00')
        ->and($run->fresh()->total_amount)->toEqual('-500.00');
});

test('the run total matches the sum of its positions after creation', function (): void {
    $first = createOrderForPaymentRunList('RE-1');
    $second = createOrderForPaymentRunList('RE-2');

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $first->contact_id,
                'iban' => 'DE89370400440532013000',
                'purpose' => 'RE-1',
                'orders' => [['order_id' => $first->getKey(), 'amount' => -1000]],
            ],
            [
                'contact_id' => $second->contact_id,
                'iban' => 'DE02120300000000202051',
                'purpose' => 'RE-2',
                'orders' => [['order_id' => $second->getKey(), 'amount' => -250.5]],
            ],
        ],
    ])->validate()->execute();

    expect($run->fresh()->total_amount)
        ->toEqual(bcround((string) $run->positions()->sum('amount'), 2));
});

test('an already offset position cannot be changed from the execute dialog', function (): void {
    $clearing = LedgerAccount::factory()->create(['tenant_id' => Tenant::default()->getKey()]);
    $creditor = LedgerAccount::factory()->create(['tenant_id' => Tenant::default()->getKey()]);

    AccountingSettings::fake([
        'auto_accept_secure_transaction_matches' => false,
        'auto_send_payment_advice' => false,
        'auto_send_reminders' => false,
        'clearing_ledger_account_id' => $clearing->getKey(),
    ]);

    $invoice = createOrderForPaymentRunList('RE-1', OrderTypeEnum::Purchase, '-500.00');
    $creditNote = createOrderForPaymentRunList('GS-1', OrderTypeEnum::PurchaseRefund, '500.00');

    Contact::query()
        ->whereKey([$invoice->contact_id, $creditNote->contact_id])
        ->update(['expense_ledger_account_id' => $creditor->getKey()]);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $invoice->contact_id,
                'iban' => 'DE89370400440532013000',
                'purpose' => 'RE-1, GS-1',
                'orders' => [
                    ['order_id' => $invoice->getKey(), 'amount' => -500],
                    ['order_id' => $creditNote->getKey(), 'amount' => 500],
                ],
            ],
        ],
    ])->validate()->execute();

    $position = $run->positions()->sole();

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $creditNote->getKey())
        ->assertReturned(false);

    expect($creditNote->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and($invoice->fresh()->payment_state)->toBeInstanceOf(Paid::class)
        ->and(OrderPaymentRun::query()
            ->where('payment_run_position_id', $position->getKey())
            ->where('order_id', $creditNote->getKey())
            ->exists())->toBeTrue()
        ->and($position->fresh()->amount)->toEqual('0.00')
        ->and($position->fresh()->orders()->count())->toBe(2)
        ->and(LedgerBooking::query()->count())->toBe(2);
});

test('opening the payment advice modal for a position queues the mail dialog', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'supplier@example.com',
        'is_invoice_address' => true,
    ]);
    $contact->update(['invoice_address_id' => $address->getKey()]);

    $order = createOrderForPaymentRunList('RE-1');
    Order::query()->whereKey($order->getKey())->update([
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
    ]);

    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
    ]);

    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'contact_id' => $contact->getKey(),
        'amount' => '-100.00',
        'end_to_end_id' => 'PR' . $paymentRun->getKey() . '-TEST',
    ]);
    $position->orders()->attach($order->getKey(), [
        'payment_run_id' => $paymentRun->getKey(),
        'amount' => '-100.00',
    ]);

    Livewire::test(PaymentRunList::class)
        ->call('openPaymentAdviceModal', $position->getKey())
        ->assertSet('documentPositionId', $position->getKey())
        ->set('selectedPrintLayouts.email', ['payment-advice'])
        ->call('createDocuments')
        ->assertHasNoErrors()
        ->assertDispatched('createFromSession');
});
