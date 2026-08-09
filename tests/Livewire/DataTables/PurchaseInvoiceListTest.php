<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PurchaseInvoiceList::class)
        ->assertOk();
});

test('mounts with default order_id is null filter', function (): void {
    $component = Livewire::test(PurchaseInvoiceList::class);

    $userFilters = $component->get('userFilters');

    expect($userFilters)->toBe([
        [
            [
                'column' => 'order_id',
                'operator' => 'is null',
                'value' => null,
            ],
        ],
    ]);
});

test('augmentItemArray sets url from media', function (): void {
    $purchaseInvoice = PurchaseInvoice::factory()->create();

    $media = $purchaseInvoice
        ->addMedia(
            UploadedFile::fake()->image('invoice.jpg')
        )
        ->toMediaCollection('purchase_invoice');

    $purchaseInvoice->load('media');

    $component = new PurchaseInvoiceList();
    $method = new ReflectionMethod($component, 'augmentItemArray');

    $itemArray = [];
    $method->invokeArgs($component, [&$itemArray, $purchaseInvoice]);

    expect($itemArray)->toHaveKey('url')
        ->and($itemArray['url'])->toBeString()
        ->and($itemArray['url'])->not->toBeEmpty()
        ->and($itemArray)->toHaveKey('media.file_name')
        ->and($itemArray['media.file_name'])->toBe('invoice.jpg');
});

test('url column is always present after loadData', function (): void {
    $component = Livewire::test(PurchaseInvoiceList::class)
        ->call('loadData');

    expect($component->get('enabledCols'))->toContain('url');
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(PurchaseInvoiceList::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('purchaseInvoiceForm.id', null)
        ->assertSet('purchaseInvoiceForm.invoice_number', null)
        ->assertSet('purchaseInvoiceForm.contact_id', null)
        ->assertOpensModal('edit-purchase-invoice-modal');
});

test('assignable orders are grouped into subscription rates and orders', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $currency = Currency::factory()->create(['is_default' => true]);
    $paymentType = PaymentType::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $priceList = PriceList::factory()->create();

    $purchaseType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
    ]);
    $subscriptionType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::PurchaseSubscription,
        'is_active' => true,
    ]);

    $orderAttributes = [
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => $currency->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'invoice_number' => null,
        'is_locked' => false,
    ];

    $contract = Order::factory()->create(array_merge($orderAttributes, [
        'order_type_id' => $subscriptionType->getKey(),
    ]));
    $rate = Order::factory()->create(array_merge($orderAttributes, [
        'order_type_id' => $purchaseType->getKey(),
        'created_from_id' => $contract->getKey(),
    ]));
    $purchaseOrder = Order::factory()->create(array_merge($orderAttributes, [
        'order_type_id' => $purchaseType->getKey(),
    ]));
    $invoiced = Order::factory()->create(array_merge($orderAttributes, [
        'order_type_id' => $purchaseType->getKey(),
        'invoice_number' => 'RE-1',
    ]));

    $component = Livewire::test(PurchaseInvoiceList::class)
        ->set('purchaseInvoiceForm.contact_id', $contact->getKey());

    $groups = collect($component->instance()->assignableOrders());
    $values = $groups->mapWithKeys(fn (array $group) => [
        $group['label'] => collect($group['value'])->pluck('value')->all(),
    ]);

    expect($values->get(__('Subscription Rates')))->toBe([$rate->getKey()])
        ->and($values->get(__('Orders')))->toContain($purchaseOrder->getKey())
        ->and($values->get(__('Orders')))->not->toContain($invoiced->getKey())
        ->and($values->get(__('Orders')))->toContain($contract->getKey());
});
