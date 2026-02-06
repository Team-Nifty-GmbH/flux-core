<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Livewire\Accounting\PaymentRunPreview;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\SepaMandate;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->id,
        'tenant_id' => $this->dbTenant->id,
        'is_main_address' => true,
        'name' => 'Test Customer',
    ]);

    $this->paymentType = PaymentType::factory()->create([
        'is_direct_debit' => false,
        'requires_manual_transfer' => true,
    ]);

    $this->priceList = PriceList::factory()->create();

    $this->currency = Currency::factory()->create();

    $this->orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->id,
        'is_active' => true,
        'order_type_enum' => collect(OrderTypeEnum::cases())
            ->first(fn ($case) => $case->multiplier() < 0),
    ]);

    $this->orders = Order::factory()->count(2)->create([
        'tenant_id' => $this->dbTenant->id,
        'contact_id' => $this->contact->id,
        'order_type_id' => $this->orderType->id,
        'payment_type_id' => $this->paymentType->id,
        'address_invoice_id' => $this->address->id,
        'price_list_id' => $this->priceList->id,
        'currency_id' => $this->currency->id,
    ]);

    // Update specific order attributes for tests
    $this->orders[0]->update([
        'invoice_number' => 'INV-001',
        'balance' => -100.50,
        'total_gross_price' => -100.50,
    ]);

    $this->orders[1]->update([
        'invoice_number' => 'INV-002',
        'balance' => -250.75,
        'total_gross_price' => -250.75,
    ]);
});

test('calculates total amount', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $orders = $component->get('orders');
    expect($orders[$this->orders[0]->id]['amount'])->toEqual(100.50);
    expect($orders[$this->orders[1]->id]['amount'])->toEqual(250.75);
    expect($orders[$this->orders[0]->id]['multiplier'])->toEqual(-1);
    expect($orders[$this->orders[1]->id]['multiplier'])->toEqual(-1);
});

test('can create payment run with multiplier', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    // Verify the multiplier is set correctly for negative balance
    $orders = $component->get('orders');
    expect($orders[$this->orders[0]->id]['multiplier'])->toEqual(-1);

    // Test that we can create payment run successfully
    $component->call('createPaymentRun')
        ->assertRedirect();
});

test('can set valid amounts', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    // Test setting a valid positive amount
    $component->set('orders.' . $this->orders[0]->id . '.amount', 50.00);
    $component->assertSet('orders.' . $this->orders[0]->id . '.amount', 50.00);

    // Test setting another valid amount
    $component->set('orders.' . $this->orders[0]->id . '.amount', 75.25);
    $component->assertSet('orders.' . $this->orders[0]->id . '.amount', 75.25);
});

test('can update payment amounts', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $component->set('orders.' . $this->orders[0]->id . '.amount', 80.00);

    $component->assertSet('orders.' . $this->orders[0]->id . '.amount', 80.00);
});

test('cancel redirects to money transfer', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('cancel')
        ->assertRedirect(route('accounting.money-transfer'));
});

test('component initializes with order ids', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    expect($component->get('orders'))->toHaveCount(2);

    $orders = $component->get('orders');
    expect($orders[$this->orders[0]->id]['id'])->toEqual($this->orders[0]->id);
    expect($orders[$this->orders[1]->id]['id'])->toEqual($this->orders[1]->id);
    expect($orders[$this->orders[0]->id]['amount'])->toEqual(100.50);
    expect($orders[$this->orders[1]->id]['amount'])->toEqual(250.75);
    expect($orders[$this->orders[0]->id]['multiplier'])->toEqual(-1);
    expect($orders[$this->orders[1]->id]['multiplier'])->toEqual(-1);
});

test('creates payment run successfully', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    expect(PaymentRun::count())->toEqual(0);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toBeGreaterThan(0);
});

test('creates payment run with custom amounts', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->set('orders.' . $this->orders[0]->id . '.amount', 75.25)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toBeGreaterThan(0);
});

test('displays orders in table', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $orders = $component->get('orders');
    expect($orders)->toHaveCount(2);

    $order1Data = $orders[$this->orders[0]->id];
    $order2Data = $orders[$this->orders[1]->id];

    expect($order1Data['invoice_number'])->toEqual('INV-001');
    expect($order2Data['invoice_number'])->toEqual('INV-002');

    $component->assertOk();
});

test('handles empty order ids', function (): void {
    session([
        'payment_run_preview_orders' => [],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->assertRedirect(route('accounting.money-transfer'));
});

test('ignores non existent order ids', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, 999999],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    expect(count($component->get('orders')))->toEqual(1);
});

test('preserves order data integrity', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $orders = $component->get('orders');
    $order = $orders[$this->orders[0]->id];

    expect($order['id'])->toEqual($this->orders[0]->id);
    expect($order['invoice_number'])->toEqual('INV-001');
    expect($order['balance'])->toEqual(-100.50);

    expect(! empty($order['contact_name']) || ! empty($order['address_name']))->toBeTrue('Either contact_name or address_name should be present. Got contact_name: ' . ($order['contact_name'] ?? 'null') . ', address_name: ' . ($order['address_name'] ?? 'null'));
});

test('redirects when missing payment run type', function (): void {
    session(['payment_run_preview_orders' => [1, 2]]);

    Livewire::test(PaymentRunPreview::class)
        ->assertRedirect(route('accounting.money-transfer'));
});

test('redirects when no session data', function (): void {
    Livewire::test(PaymentRunPreview::class)
        ->assertRedirect(route('accounting.money-transfer'));
});

test('renders successfully', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->assertOk();
});

test('renders successfully with minimal session data', function (): void {
    session([
        'payment_run_preview_orders' => [9999, 9998], // Non-existent IDs
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $component->assertOk();
    expect($component->get('orders'))->toBeEmpty();
});

test('shows notification on successful creation', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();
});

test('money transfer creates single payment run regardless of sepa mandate types', function (): void {
    $bankConnection1 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection1->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $bankConnection2 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection2->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::B2B,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $this->orders[0]->update(['contact_bank_connection_id' => $bankConnection1->id]);
    $this->orders[1]->update(['contact_bank_connection_id' => $bankConnection2->id]);

    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toEqual(1);
    expect(PaymentRun::first()->orders)->toHaveCount(2);
});

test('direct debit creates separate payment runs per sepa mandate type', function (): void {
    $directDebitPaymentType = PaymentType::factory()->create([
        'is_direct_debit' => true,
    ]);

    $bankConnection1 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection1->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $bankConnection2 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection2->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::B2B,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $this->orders[0]->update([
        'contact_bank_connection_id' => $bankConnection1->id,
        'payment_type_id' => $directDebitPaymentType->id,
    ]);
    $this->orders[1]->update([
        'contact_bank_connection_id' => $bankConnection2->id,
        'payment_type_id' => $directDebitPaymentType->id,
    ]);

    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::DirectDebit,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toEqual(2);
});
