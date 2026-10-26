<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
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

test('finishing reports a position without vat rate on that position', function (): void {
    $purchaseInvoice = PurchaseInvoice::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => Contact::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create(['is_default' => true])->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create(['requires_manual_transfer' => false])
            ->getKey(),
        'order_type_id' => OrderType::factory()
            ->create(['order_type_enum' => OrderTypeEnum::Purchase, 'is_active' => true])
            ->getKey(),
        'invoice_number' => 'RE-4711',
        'invoice_date' => '2026-10-01',
        'total_gross_price' => 300,
        'is_net' => true,
        'order_id' => null,
    ]);
    $purchaseInvoice
        ->addMedia(UploadedFile::fake()->image('invoice.jpg'))
        ->toMediaCollection('purchase_invoice');
    $purchaseInvoice->purchaseInvoicePositions()->create([
        'name' => 'Anzahl',
        'amount' => 6,
        'unit_price' => 50,
        'total_price' => 300,
        'vat_rate_id' => null,
    ]);

    Livewire::test(PurchaseInvoiceList::class)
        ->call('edit', $purchaseInvoice->getKey())
        ->call('finish')
        ->assertReturned(false)
        ->assertHasErrors('purchaseInvoiceForm.purchase_invoice_positions.0.vat_rate_id');

    expect($purchaseInvoice->refresh()->order_id)->toBeNull();
});
