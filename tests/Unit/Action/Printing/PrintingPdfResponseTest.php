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
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $address = Address::factory()->create([
        'company' => Str::uuid()->toString(),
        'contact_id' => $contact->getKey(),
    ]);

    $priceList = PriceList::factory()->create();
    $currency = Currency::factory()->create(['is_default' => true]);
    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'print_layouts' => ['offer', 'invoice'],
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create(['is_default' => false]);

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

test('the pdf print result is a responsable that streams a pdf', function (): void {
    $this->withoutVite();

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'offer',
        'html' => false,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(Responsable::class);

    $response = $result->toResponse(request());

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->getContent())->toStartWith('%PDF-');
});
