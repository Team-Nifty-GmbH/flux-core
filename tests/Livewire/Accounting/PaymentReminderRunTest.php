<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\PaymentReminderRun;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
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
        ->create();

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
