<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Features\SignaturePublicLink;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    $client = Client::factory()->create([
        'is_default' => true,
    ]);
    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);
    $contact = Contact::factory()->create([
        'client_id' => $client->id,
    ]);
    $priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $client, relationship: 'clients')
        ->create([
            'is_default' => true,
        ]);

    $orderType = OrderType::factory()->create([
        'client_id' => $client->id,
        'order_type_enum' => OrderTypeEnum::Order->value,
        'print_layouts' => ['invoice'],
    ]);

    $address = Address::factory()->create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);

    $this->order = Order::factory()->create([
        'client_id' => $client->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $address->id,
        'price_list_id' => $priceList->id,
        'payment_type_id' => $paymentType->id,
        'order_type_id' => $orderType->id,
    ]);
});

test('renders successfully', function (): void {
    $this->withoutVite();

    Livewire::test(
        SignaturePublicLink::class,
        [
            'uuid' => $this->order->uuid,
            'model' => $this->order->getMorphClass(),
            'printView' => array_keys($this->order->resolvePrintViews())[0],
        ]
    )
        ->assertStatus(200)
        ->assertSet('uuid', $this->order->uuid)
        ->assertSet('model', $this->order->getMorphClass())
        ->assertSet('printView', array_keys($this->order->resolvePrintViews())[0])
        ->assertSet('signature.stagedFiles', [])
        ->assertSet('signature.id', null);
});

test('upload signature', function (): void {
    $this->withoutVite();

    Storage::fake('local');
    $file = UploadedFile::fake()->image('signature.png');

    Livewire::test(
        SignaturePublicLink::class,
        [
            'uuid' => $this->order->uuid,
            'model' => $this->order->getMorphClass(),
            'printView' => array_keys($this->order->resolvePrintViews())[0],
        ]
    )
        ->set('signature.file', $file)
        ->set('signature.custom_properties.name', 'John Doe')
        ->assertCount('signature.stagedFiles', 1)
        ->call('save')
        ->assertReturned(true);

    $this->assertDatabaseHas('media', [
        'model_id' => $this->order->id,
        'model_type' => $this->order->getMorphClass(),
        'collection_name' => 'signature',
        'name' => 'signature-' . array_keys($this->order->resolvePrintViews())[0],
        'custom_properties->name' => 'John Doe',
    ]);
});
