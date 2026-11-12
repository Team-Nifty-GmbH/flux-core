<?php

use FluxErp\Actions\Printing;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Number;

test('the payment advice lists both invoices, the credit note, the net total and the end to end id', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_invoice_address' => true,
    ]);
    $contact->update(['invoice_address_id' => $address->getKey()]);

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();

    $makeOrder = fn (string $invoiceNumber, string $grossPrice) => Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'invoice_number' => $invoiceNumber,
        'invoice_date' => now()->toDateString(),
        'total_gross_price' => $grossPrice,
    ]);

    $invoiceOne = $makeOrder('RE-2026-001', '1000.00');
    $invoiceTwo = $makeOrder('RE-2026-002', '500.00');
    $creditNote = $makeOrder('GS-2026-001', '200.00');

    $paymentRun = PaymentRun::query()->create([
        'payment_run_type_enum' => 'money_transfer',
        'state' => 'open',
        'instructed_execution_date' => now()->toDateString(),
    ]);

    $position = PaymentRunPosition::factory()->create([
        'payment_run_id' => $paymentRun->getKey(),
        'contact_id' => $contact->getKey(),
        'iban' => 'DE89370400440532013000',
        'account_holder' => 'Muster GmbH',
        'amount' => '-1300.00',
        'end_to_end_id' => 'PR' . $paymentRun->getKey() . '-TEST',
    ]);

    $position->orders()->attach([
        $invoiceOne->getKey() => ['payment_run_id' => $paymentRun->getKey(), 'amount' => '-1000.00'],
        $invoiceTwo->getKey() => ['payment_run_id' => $paymentRun->getKey(), 'amount' => '-500.00'],
        $creditNote->getKey() => ['payment_run_id' => $paymentRun->getKey(), 'amount' => '200.00'],
    ]);

    $html = Printing::make([
        'model_type' => $position->getMorphClass(),
        'model_id' => $position->getKey(),
        'view' => 'payment-advice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    expect($html)->toContain('RE-2026-001')
        ->and($html)->toContain('RE-2026-002')
        ->and($html)->toContain('GS-2026-001')
        ->and($html)->toContain($position->end_to_end_id)
        ->and($html)->toContain(Number::currency('1300.00'));
});
