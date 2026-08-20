<?php

use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertOk();
});

test('marking orders as paid dispatches one job per open order', function (): void {
    Queue::fake();

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderData = [
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ];

    $open = Order::factory()->create($orderData + ['payment_state' => Open::class]);
    $alreadyPaid = Order::factory()->create($orderData + ['payment_state' => Paid::class]);

    Livewire::test(OrderList::class)
        ->set('selected', [$open->getKey(), $alreadyPaid->getKey()])
        ->call('markAsPaid')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    // the order that is settled already is filtered out
    Queue::assertPushed(
        UpdateLockedOrder::class,
        fn (UpdateLockedOrder $job): bool => $job->getData('id') === $open->getKey()
            && $job->getData('payment_state') === Paid::class
    );
    Queue::assertPushed(UpdateLockedOrder::class, 1);
});

test('the dispatched job settles the order', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'payment_state' => Open::class,
        'is_locked' => true,
    ]);

    UpdateLockedOrder::make([
        'id' => $order->getKey(),
        'payment_state' => Paid::class,
    ])
        ->validate()
        ->execute();

    expect($order->refresh()->payment_state)->toBeInstanceOf(Paid::class);
});

test('marking orders as paid does nothing without a selection', function (): void {
    Queue::fake();

    Livewire::test(OrderList::class)
        ->set('selected', [])
        ->call('markAsPaid')
        ->assertOk()
        ->assertHasNoErrors();

    Queue::assertNothingPushed();
});

test('creating payment reminders from the order list switches the modal to reminder layouts', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-900',
        'payment_reminder_current_level' => 0,
    ]);

    $component = Livewire::test(OrderList::class)
        ->set('selected', [$order->getKey()])
        ->call('openCreatePaymentRemindersModal')
        ->assertOk()
        ->assertSet('createsPaymentReminders', true);

    expect($component->get('printLayouts'))->not->toBeEmpty();

    // Opening the regular document modal switches back to the order layouts.
    $component->call('openCreateDocumentsModal')
        ->assertSet('createsPaymentReminders', false);
});

test('creating payment reminders skips orders without a mailable address', function (): void {
    $withEmail = Contact::factory()->create();
    $addressWithEmail = Address::factory()->create([
        'contact_id' => $withEmail->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $withoutEmail = Contact::factory()->create();
    $addressWithoutEmail = Address::factory()->create([
        'contact_id' => $withoutEmail->getKey(),
        'email_primary' => null,
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $makeOrder = fn (Contact $contact, Address $address, string $invoiceNumber) => Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'is_locked' => true,
        'invoice_number' => $invoiceNumber,
        'payment_reminder_current_level' => 0,
    ]);

    $mailable = $makeOrder($withEmail, $addressWithEmail, 'INV-2026-901');
    $unmailable = $makeOrder($withoutEmail, $addressWithoutEmail, 'INV-2026-902');

    Livewire::test(OrderList::class)
        ->set('selected', [$mailable->getKey(), $unmailable->getKey()])
        ->call('openCreatePaymentRemindersModal')
        ->call('createDocuments')
        ->assertOk();

    expect(PaymentReminder::query()->where('order_id', $mailable->getKey())->exists())->toBeTrue()
        ->and(PaymentReminder::query()->where('order_id', $unmailable->getKey())->exists())->toBeFalse();
});
