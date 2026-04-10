<?php

use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
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
