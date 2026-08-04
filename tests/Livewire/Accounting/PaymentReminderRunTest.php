<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Jobs\Accounting\SendPaymentReminderJob;
use FluxErp\Livewire\Accounting\PaymentReminderRun;
use FluxErp\Livewire\EditMail;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('payment reminder run renders', function (): void {
    Livewire::test(PaymentReminderRun::class)
        ->assertOk();
});

test('payment reminder run lists due orders and preselects them', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-200',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    Livewire::test(PaymentReminderRun::class)
        ->assertSet('groups', fn (array $groups) => count($groups) === 1)
        ->assertSet('selectedOrders', fn (array $ids) => in_array((string) $order->getKey(), $ids, true))
        // Deselecting the whole group clears the selection.
        ->call('toggleGroup', $contact->getKey() . '-1')
        ->assertSet('selectedOrders', [])
        // A non-matching level filter yields no groups.
        ->set('filterLevel', '3')
        ->assertSet('groups', []);
});

test('searches due orders by the contact name', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
        'company' => 'Wunderlich Verlag GmbH',
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-201',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    Livewire::test(PaymentReminderRun::class)
        ->set('search', 'Wunderlich')
        ->assertSet('groups', fn (array $groups) => count($groups) === 1)
        ->set('search', 'no such company')
        ->assertSet('groups', []);
});

test('sent orders disappear from the list without a manual reload', function (): void {
    Queue::fake();

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-201',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    // Only a reminder level with a mail template can actually be sent, and only
    // sent orders may disappear from the list.
    PaymentReminderText::factory()->create([
        'reminder_level' => 1,
        'email_template_id' => EmailTemplate::factory()->create()->getKey(),
    ]);

    // The sends run as a queued batch; the database has not changed yet when the
    // component reloads, so the sent orders must be hidden optimistically.
    Livewire::actingAs($this->user)
        ->test(PaymentReminderRun::class)
        ->assertSet('groups', fn (array $groups) => count($groups) === 1)
        ->call('sendSelected')
        ->assertSet('groups', fn (array $groups) => $groups === [])
        ->assertSet('selectedOrders', []);
});

test('a single invoice can be edited as a mail before it is sent', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-203',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    $emailTemplate = EmailTemplate::factory()->create();
    PaymentReminderText::factory()->create([
        'reminder_level' => 1,
        'email_template_id' => $emailTemplate->getKey(),
    ]);

    // The mail dialog is the whole point: the user edits the text before sending,
    // which only works for a single invoice, never for the bulk run.
    Livewire::actingAs($this->user)
        ->test(PaymentReminderRun::class)
        ->call('editMail', $order->getKey())
        ->assertSet('documentOrderId', $order->getKey())
        ->set('selectedPrintLayouts.email', ['payment-reminder'])
        ->call('createDocuments')
        ->assertHasNoErrors()
        ->assertDispatched('createFromSession');

    expect(PaymentReminder::query()->where('order_id', $order->getKey())->count())->toBe(1)
        ->and(PaymentReminder::query()->where('order_id', $order->getKey())->value('reminder_level'))
        ->toBe(1);
});

test('the reminder mail defaults to the template of the reminder level', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-204',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    $emailTemplate = EmailTemplate::factory()->create();
    PaymentReminderText::factory()->create([
        'reminder_level' => 1,
        'email_template_id' => $emailTemplate->getKey(),
    ]);

    Livewire::actingAs($this->user)
        ->test(PaymentReminderRun::class)
        ->call('editMail', $order->getKey())
        ->set('selectedPrintLayouts.email', ['payment-reminder'])
        ->call('createDocuments')
        ->assertDispatched('createFromSession', function (string $event, array $params) use ($emailTemplate): bool {
            $messages = session()->get($params['key']);

            return count($messages) === 1
                && $messages[0]['default_template_id'] === $emailTemplate->getKey()
                && $messages[0]['to'] === ['reminder@example.com'];
        });
});

test('the mail dialog shows the rendered reminder text instead of the placeholders', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-205',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    // Mirrors how the editor stores a variable in a real reminder template.
    $emailTemplate = EmailTemplate::factory()->create([
        'html_body' => '<p>Rechnung <span data-type="blade-variable" '
            . 'data-value="$paymentReminder-&gt;order-&gt;invoice_number">Rechnungsnummer</span></p>',
    ]);
    PaymentReminderText::factory()->create([
        'reminder_level' => 1,
        'email_template_id' => $emailTemplate->getKey(),
    ]);

    $sessionKey = null;

    Livewire::actingAs($this->user)
        ->test(PaymentReminderRun::class)
        ->call('editMail', $order->getKey())
        ->set('selectedPrintLayouts.email', ['payment-reminder'])
        ->call('createDocuments')
        ->assertDispatched('createFromSession', function (string $event, array $params) use (&$sessionKey): bool {
            $sessionKey = $params['key'];

            return true;
        });

    // The user has to see the finished text, not the raw placeholder markup.
    Livewire::actingAs($this->user)
        ->test(EditMail::class)
        ->call('createFromSession', $sessionKey)
        ->assertSet('mailMessage.html_body', fn (?string $body): bool => str_contains($body ?? '', 'INV-2026-205')
            && ! str_contains($body ?? '', 'blade-variable')
        );
});

test('orders that cannot be sent stay in the list instead of looking sent', function (): void {
    Queue::fake();

    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'reminder@example.com',
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create([
            'is_direct_debit' => false,
        ]);

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-202',
        'payment_reminder_current_level' => 0,
    ]);

    Order::query()->whereKey($order->getKey())->update([
        'balance' => 250,
        'payment_state' => 'open',
        'payment_reminder_next_date' => now()->subDays(5)->toDateString(),
    ]);

    // The reminder level has a text but no email template, so nothing can be mailed.
    PaymentReminderText::factory()->create([
        'reminder_level' => 1,
        'email_template_id' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(PaymentReminderRun::class)
        ->assertSet('groups', fn (array $groups) => count($groups) === 1)
        ->call('sendSelected')
        ->assertSet('groups', fn (array $groups) => count($groups) === 1)
        ->assertSet('sentOrderIds', []);

    Queue::assertNotPushed(SendPaymentReminderJob::class);
});
