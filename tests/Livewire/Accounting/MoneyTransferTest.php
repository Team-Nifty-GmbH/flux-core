<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\MoneyTransfer;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

function createPayableOrder(TestCase $test, OrderType $orderType, string $balance, ?int $contactId = null): Order
{
    $contact = $contactId
        ? resolve_static(Contact::class, 'query')->findOrFail($contactId)
        : Contact::factory()->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    $paymentType = PaymentType::factory()->create([
        'is_direct_debit' => false,
        'requires_manual_transfer' => true,
    ]);

    $order = Order::factory()->create([
        'tenant_id' => $test->dbTenant->id,
        'contact_id' => $contact->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
    ]);

    $order->update([
        'invoice_number' => 'INV-' . $order->getKey(),
        'balance' => $balance,
        'total_gross_price' => $balance,
    ]);

    return $order->fresh();
}

test('renders successfully', function (): void {
    Livewire::test(MoneyTransfer::class)
        ->assertOk();
});

test('the money transfer list contains supplier credit notes', function (): void {
    $purchaseType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
    ]);
    $refundType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::PurchaseRefund,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $invoice = createPayableOrder($this, $purchaseType, '-500.00');
    $creditNote = createPayableOrder($this, $refundType, '200.00');

    Livewire::test(MoneyTransfer::class)
        ->assertSee($invoice->invoice_number)
        ->assertSee($creditNote->invoice_number);
});

test('a purchase order with a positive balance does not appear in the list', function (): void {
    $purchaseType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $overpaidInvoice = createPayableOrder($this, $purchaseType, '500.00');

    Livewire::test(MoneyTransfer::class)
        ->assertDontSee($overpaidInvoice->invoice_number);
});

test('a sales order type does not appear in the list', function (): void {
    $salesType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $salesOrder = createPayableOrder($this, $salesType, '200.00');

    Livewire::test(MoneyTransfer::class)
        ->assertDontSee($salesOrder->invoice_number);
});
