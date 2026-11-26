<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\PaymentRunList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\States\Order\PaymentState\Open;
use Livewire\Livewire;

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

    $first = createPaymentRunOrder();
    $second = createPaymentRunOrder();

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

    $order = createPaymentRunOrder();

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

test('a position left at zero or pointing against the run is dropped entirely', function (
    string $paymentRunType,
    array $droppedAmounts,
    string $untouchedAmount
): void {
    $droppedOrders = collect($droppedAmounts)
        ->keys()
        ->map(fn (int $index) => createPaymentRunOrder(attributes: ['invoice_number' => 'RE-1' . $index]));
    $untouched = createPaymentRunOrder(attributes: ['invoice_number' => 'RE-2']);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => $paymentRunType,
        'positions' => [
            [
                'contact_id' => $droppedOrders->first()->contact_id,
                'iban' => 'DE89370400440532013000',
                'orders' => $droppedOrders
                    ->map(fn (Order $order, int $index) => [
                        'order_id' => $order->getKey(),
                        'amount' => $droppedAmounts[$index],
                    ])
                    ->all(),
            ],
            [
                'contact_id' => $untouched->contact_id,
                'iban' => 'DE02120300000000202051',
                'purpose' => 'RE-2',
                'orders' => [
                    ['order_id' => $untouched->getKey(), 'amount' => $untouchedAmount],
                ],
            ],
        ],
    ])->validate()->execute();

    $droppedPosition = $run->positions()->where('contact_id', $droppedOrders->first()->contact_id)->sole();

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $droppedOrders->first()->getKey());

    expect(PaymentRunPosition::query()->whereKey($droppedPosition->getKey())->exists())->toBeFalse()
        ->and(PaymentRun::query()->whereKey($run->getKey())->exists())->toBeTrue()
        ->and($run->fresh()->positions()->count())->toBe(1)
        ->and($run->fresh()->total_amount)->toEqual($untouchedAmount);

    foreach ($droppedOrders->skip(1) as $remainingOrder) {
        expect($remainingOrder->fresh()->payment_state)->toBeInstanceOf(Open::class);
    }
})->with([
    'emptied' => ['money_transfer', [-1000], '-300.00'],
    'pointing against a money transfer' => ['money_transfer', [-1000, 200], '-300.00'],
    'left at zero' => ['money_transfer', [-1000, -200, 200], '-300.00'],
    'pointing against a direct debit' => ['direct_debit', [1000, -200], '300.00'],
]);

test('removing the credit note from a refund position of an overpaid sales invoice raises the transfer again', function (): void {
    $invoice = createPaymentRunOrder(OrderTypeEnum::Order, '-404.12', ['invoice_number' => 'RE-1']);
    $creditNote = createPaymentRunOrder(OrderTypeEnum::PurchaseRefund, '200.00', ['invoice_number' => 'GS-1']);

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

    $position = $run->positions()->sole();

    Livewire::test(PaymentRunList::class)
        ->call('edit', $run)
        ->call('removeOrder', $creditNote->getKey());

    expect($position->fresh()->amount)->toEqual('-404.12')
        ->and($position->fresh()->orders()->pluck('orders.id')->all())->toBe([$invoice->getKey()])
        ->and($run->fresh()->total_amount)->toEqual('-404.12')
        ->and($creditNote->fresh()->payment_state)->toBeInstanceOf(Open::class);
});

test('removing an order rebuilds the purpose but keeps the end to end id', function (): void {
    $first = createPaymentRunOrder(attributes: ['invoice_number' => 'RE-1']);
    $second = createPaymentRunOrder(attributes: ['invoice_number' => 'RE-2']);

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
    $first = createPaymentRunOrder(attributes: ['invoice_number' => 'RE-1']);
    $second = createPaymentRunOrder(attributes: ['invoice_number' => 'RE-2']);

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
