<?php

use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Livewire\Contact\Attachments;
use FluxErp\Livewire\DataTables\MediaGrid;
use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use FluxErp\Livewire\Settings\BlockedFiles;
use FluxErp\Models\Address;
use FluxErp\Models\BlockedFile;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\PurchaseInvoice;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->storeFile = function (string $modelType, int $modelId): Media {
        $path = tempnam(sys_get_temp_dir(), 'file_');
        file_put_contents($path, 'logo content');

        return UploadMedia::make([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'media' => $path,
            'file_name' => 'logo.txt',
        ])
            ->force()
            ->validate()
            ->execute();
    };
});

test('the media grid blocks a file', function (): void {
    $media = ($this->storeFile)(morph_alias(Contact::class), $this->contact->getKey());

    Livewire::test(MediaGrid::class)
        ->call('blockFile', $media->getKey())
        ->assertReturned(true);

    expect(BlockedFile::query()->where('hash', md5('logo content'))->exists())->toBeTrue()
        ->and(Media::query()->whereKey($media->getKey())->exists())->toBeFalse();
});

test('an attachments tab blocks a file', function (): void {
    $media = ($this->storeFile)(morph_alias(Contact::class), $this->contact->getKey());

    Livewire::test(Attachments::class)
        ->call('blockFile', $media->getKey())
        ->assertReturned(true);

    expect(BlockedFile::query()->where('hash', md5('logo content'))->exists())->toBeTrue();
});

test('the purchase invoice list blocks the file of a purchase invoice without order', function (): void {
    $purchaseInvoice = PurchaseInvoice::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $media = ($this->storeFile)(morph_alias(PurchaseInvoice::class), $purchaseInvoice->getKey());
    PurchaseInvoice::query()->whereKey($purchaseInvoice->getKey())->update(['media_id' => $media->getKey()]);

    Livewire::test(PurchaseInvoiceList::class)
        ->call('blockFile', $purchaseInvoice->getKey())
        ->assertReturned(true);

    expect(PurchaseInvoice::query()->withTrashed()->whereKey($purchaseInvoice->getKey())->exists())->toBeFalse();
});

test('the purchase invoice list does not block the file of a purchase invoice with order', function (): void {
    $address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $order = Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $this->contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'order_type_id' => OrderType::factory()->create()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $purchaseInvoice = PurchaseInvoice::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $media = ($this->storeFile)(morph_alias(PurchaseInvoice::class), $purchaseInvoice->getKey());
    PurchaseInvoice::query()
        ->whereKey($purchaseInvoice->getKey())
        ->update(['media_id' => $media->getKey(), 'order_id' => $order->getKey()]);

    Livewire::test(PurchaseInvoiceList::class)
        ->call('blockFile', $purchaseInvoice->getKey())
        ->assertReturned(false);

    expect(PurchaseInvoice::query()->whereKey($purchaseInvoice->getKey())->exists())->toBeTrue();
});

test('the settings page lists blocked files and unblocks them', function (): void {
    $blockedFile = BlockedFile::query()->create(['hash' => md5('logo content'), 'file_name' => 'logo.txt']);

    $data = Livewire::test(BlockedFiles::class)
        ->assertOk()
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->pluck('id')->all())->toBe([$blockedFile->getKey()]);

    Livewire::test(BlockedFiles::class)
        ->call('delete', $blockedFile->getKey())
        ->assertReturned(true);

    expect(BlockedFile::query()->count())->toBe(0);
});

test('the purchase invoice list knows whether a row has an order', function (): void {
    $purchaseInvoice = PurchaseInvoice::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    $data = Livewire::test(PurchaseInvoiceList::class)
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(collect(data_get($data, 'data'))->firstWhere('id', $purchaseInvoice->getKey()))
        ->toHaveKey('order_id');
});
