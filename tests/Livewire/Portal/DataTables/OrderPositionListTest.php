<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Database\Seeders\OrderPositionTableSeeder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Portal\DataTables\OrderPositionList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    $priceList = PriceList::factory()->create();

    $this->order = Order::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'is_locked' => false,
    ]);

    Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();

    (new OrderPositionTableSeeder())->run();
});

test('renders successfully', function (): void {
    Livewire::test(OrderPositionList::class, ['orderId' => $this->order->id])
        ->assertStatus(200);
});
