<?php

use FluxErp\Actions\Printing;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $address = Address::factory()->create([
        'company' => Str::uuid()->toString(),
        'contact_id' => $contact->getKey(),
    ]);

    $orderType = OrderType::factory()
        ->create([
            'print_layouts' => ['invoice'],
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => Language::factory()->create()->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create(['is_default' => true])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'is_locked' => false,
    ]);
});

test('continuation-page header keeps the subject heading within its allotted vertical space', function (): void {
    $this->withoutVite();

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    // dompdf applies UA-style margins to <h2>; on the fixed continuation-page
    // header that pushes the box past its -20mm top offset and overlaps the
    // first line of body content on page 2+. The header's <h2> must zero its
    // margins so the header's rendered height stays within 20mm.
    expect($html)->toMatch('/<header\b[^>]*>.*?<h2\b[^>]*style="[^"]*\bmargin:\s*0\b/s');
});

test('the tenant logo travels in the document instead of being fetched over http', function (): void {
    $this->withoutVite();

    config(['media-library.queue_conversions_by_default' => false]);

    $this->dbTenant->addMedia(UploadedFile::fake()->image('logo.png'))
        ->toMediaCollection('logo');
    $this->dbTenant->addMedia(UploadedFile::fake()->image('logo-small.png'))
        ->toMediaCollection('logo_small');

    $html = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'invoice',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    expect($html)->toContain('src="data:image/png;base64,')
        ->and($html)->not->toMatch('/src="https?:\/\/[^"]*logo/');
});

test('a tenant without a small logo renders no empty image', function (string $view, string $model): void {
    $this->withoutVite();

    $printable = $model === 'order' ? $this->order : $this->order->addressInvoice;

    $html = Printing::make([
        'model_type' => $printable->getMorphClass(),
        'model_id' => $printable->getKey(),
        'view' => $view,
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute()
        ->toHtml();

    expect($html)->not->toContain('src=""');
})->with([
    ['invoice', 'order'],
    ['address-label', 'address'],
]);
