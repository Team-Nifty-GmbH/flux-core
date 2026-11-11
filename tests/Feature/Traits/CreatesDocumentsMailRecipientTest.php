<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\View\Printing\Order\Invoice;

afterEach(function (): void {
    Order::$registeredPrintViews = [];
});

test('a registered invoice view is mailed to the invoice address', function (): void {
    Order::registerPrintView('registered-invoice', Invoice::class);

    $contact = Contact::factory()->create();
    $mainAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'main@example.com',
    ]);
    $invoiceAddress = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'email_primary' => 'invoice@example.com',
    ]);
    $contact->update(['main_address_id' => $mainAddress->getKey()]);

    $order = Order::factory()->create([
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $invoiceAddress->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => Language::default()->getKey(),
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'print_layouts' => ['registered-invoice'],
        ])->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $to = (new ReflectionMethod(OrderList::class, 'getTo'))
        ->invoke(app(OrderList::class), $order->refresh(), ['registered-invoice']);

    expect($to)->toBe(['invoice@example.com']);
});
