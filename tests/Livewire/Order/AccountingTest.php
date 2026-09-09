<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Order\Accounting;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_active' => true,
        ]);

    $priceList = PriceList::factory()->create();

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'is_locked' => false,
    ]);
});

test('renders successfully', function (): void {
    $form = new OrderForm(Livewire::new(Accounting::class), 'order');
    $form->fill($this->order);

    Livewire::test(Accounting::class, ['order' => $form])
        ->assertOk();
});

test('can reset payment reminder level', function (): void {
    $this->order->update([
        'payment_reminder_current_level' => 2,
        'payment_reminder_next_date' => now()->addDays(7),
        'invoice_number' => 'INV-123',
    ]);

    $form = new OrderForm(Livewire::new(Accounting::class), 'order');
    $form->fill($this->order);

    Livewire::test(Accounting::class, ['order' => $form])
        ->set('newPaymentReminderLevel', 0)
        ->call('resetPaymentReminderLevel')
        ->assertHasNoErrors()
        ->assertOk();

    expect($this->order->fresh()->payment_reminder_current_level)->toBe(0);
});

test('can set payment reminder level to specific value', function (): void {
    $this->order->update([
        'payment_reminder_current_level' => 3,
        'payment_reminder_next_date' => now()->addDays(7),
        'invoice_number' => 'INV-123',
    ]);

    $form = new OrderForm(Livewire::new(Accounting::class), 'order');
    $form->fill($this->order);

    Livewire::test(Accounting::class, ['order' => $form])
        ->set('newPaymentReminderLevel', 1)
        ->call('resetPaymentReminderLevel')
        ->assertOk();

    expect($this->order->fresh()->payment_reminder_current_level)->toBe(1);
});

test('preview payment purpose pattern returns matching transactions only', function (): void {
    $bankConnection = FluxErp\Models\BankConnection::factory()->create();
    $matching = FluxErp\Models\Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'purpose' => 'DARLEHEN 0047123456 Rate 07/2026',
    ]);
    FluxErp\Models\Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'purpose' => 'Miete Juli',
    ]);

    $form = new OrderForm(Livewire::new(Accounting::class), 'order');
    $form->fill($this->order);

    $matches = Livewire::test(Accounting::class, ['order' => $form])
        ->instance()
        ->previewPaymentPurposePattern('darlehen 0047');

    expect($matches)->toHaveCount(1)
        ->and($matches[0]['purpose'])->toContain('DARLEHEN 0047123456');
});

test('preview payment purpose pattern treats like wildcards as literals', function (): void {
    $bankConnection = FluxErp\Models\BankConnection::factory()->create();
    FluxErp\Models\Transaction::factory()->create([
        'bank_connection_id' => $bankConnection->getKey(),
        'purpose' => 'Darlehen 123',
    ]);

    $form = new OrderForm(Livewire::new(Accounting::class), 'order');
    $form->fill($this->order);

    $component = Livewire::test(Accounting::class, ['order' => $form]);

    expect($component->instance()->previewPaymentPurposePattern('%'))->toBeEmpty()
        ->and($component->instance()->previewPaymentPurposePattern(''))->toBeEmpty();
});
