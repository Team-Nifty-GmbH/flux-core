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
use FluxErp\View\Printing\PrintableView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'company' => Str::uuid()->toString(),
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
    ]);

    $priceList = PriceList::factory()->create();

    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $language = Language::factory()->create();

    $orderType = OrderType::factory()
        ->create([
            'print_layouts' => ['offer', 'invoice'],
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_default' => false,
        ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'is_locked' => false,
    ]);
});

test('can render html preview', function (): void {
    $this->withoutVite();

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'offer',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(Htmlable::class);
    $html = $result->toHtml();

    $this->assertStringContainsString(data_get($this->order->address_invoice, 'company'), $html);
    $this->assertStringContainsString('Offer ' . $this->order->order_number, $html);
    $this->assertStringContainsString('Sum net', $html);
    $this->assertStringContainsString('Total Gross', $html);
});

test('can render pdf preview', function (): void {
    $this->withoutVite();

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'offer',
        'preview' => true,
        'html' => false,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(PrintableView::class);
    expect($result->pdf)->not->toBeEmpty();

    $pdf = $result->pdf->output();

    expect($pdf)->not->toBeEmpty();
    expect($pdf)->toStartWith('%PDF-');
    $this->assertStringContainsString('%%EOF', $pdf);
    $this->assertStringContainsString('/Pages', $pdf);
    $this->assertStringContainsString('/Type /Page', $pdf);
    $this->assertStringContainsString('/Contents', $pdf);
    expect(strlen($pdf))->toBeGreaterThan(1000);
});
