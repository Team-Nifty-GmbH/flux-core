<?php

use FluxErp\Actions\BlockedFile\BlockFile;
use FluxErp\Actions\BlockedFile\DeleteBlockedFile;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\PurchaseInvoice\BlockPurchaseInvoiceFile;
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

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->storeFile = function (string $content, string $modelType, int $modelId, string $collection = 'default'): Media {
        $path = tempnam(sys_get_temp_dir(), 'file_');
        file_put_contents($path, $content);

        return UploadMedia::make([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'media' => $path,
            'file_name' => 'logo.txt',
            'collection_name' => $collection,
        ])
            ->force()
            ->validate()
            ->execute();
    };

    $this->makePurchaseInvoice = function (?int $orderId = null): PurchaseInvoice {
        $purchaseInvoice = PurchaseInvoice::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
        ]);
        $media = ($this->storeFile)(
            'logo content',
            morph_alias(PurchaseInvoice::class),
            $purchaseInvoice->getKey()
        );

        PurchaseInvoice::query()
            ->whereKey($purchaseInvoice->getKey())
            ->update(['media_id' => $media->getKey(), 'order_id' => $orderId]);

        return $purchaseInvoice->refresh();
    };
});

test('blocking a file stores its fingerprint and deletes the file for good', function (): void {
    $media = ($this->storeFile)('logo content', morph_alias(Contact::class), $this->contact->getKey());
    $path = $media->getPath();

    $blockedFile = BlockFile::make(['media_id' => $media->getKey()])->validate()->execute();

    expect($blockedFile->hash)->toBe(md5('logo content'))
        ->and($blockedFile->file_name)->toBe('logo.txt')
        ->and($blockedFile->size)->toBe(12)
        ->and(Media::query()->whereKey($media->getKey())->exists())->toBeFalse()
        ->and(file_exists($path))->toBeFalse();
});

test('blocking the same content twice keeps one entry', function (): void {
    $first = ($this->storeFile)('logo content', morph_alias(Contact::class), $this->contact->getKey());
    $second = ($this->storeFile)('logo content', morph_alias(Contact::class), $this->contact->getKey());

    BlockFile::make(['media_id' => $first->getKey()])->validate()->execute();
    BlockFile::make(['media_id' => $second->getKey()])->validate()->execute();

    expect(BlockedFile::query()->count())->toBe(1);
});

test('rejects a media without readable file', function (): void {
    $media = ($this->storeFile)('logo content', morph_alias(Contact::class), $this->contact->getKey());
    unlink($media->getPath());

    BlockFile::assertValidationErrors(['media_id' => $media->getKey()], 'media_id');
});

test('unblocking allows the file to be stored again', function (): void {
    $media = ($this->storeFile)('logo content', morph_alias(Contact::class), $this->contact->getKey());
    $blockedFile = BlockFile::make(['media_id' => $media->getKey()])->validate()->execute();

    DeleteBlockedFile::make(['id' => $blockedFile->getKey()])->validate()->execute();

    expect(BlockedFile::query()->count())->toBe(0)
        ->and(($this->storeFile)('logo content', morph_alias(Contact::class), $this->contact->getKey()))
        ->toBeInstanceOf(Media::class);
});

test('blocking the file of a purchase invoice without order deletes the purchase invoice', function (): void {
    $purchaseInvoice = ($this->makePurchaseInvoice)();
    $mediaId = $purchaseInvoice->media_id;

    BlockPurchaseInvoiceFile::make(['id' => $purchaseInvoice->getKey()])->validate()->execute();

    expect(BlockedFile::query()->where('hash', md5('logo content'))->exists())->toBeTrue()
        ->and(PurchaseInvoice::query()->withTrashed()->whereKey($purchaseInvoice->getKey())->exists())->toBeFalse()
        ->and(Media::query()->whereKey($mediaId)->exists())->toBeFalse();
});

test('the file of a purchase invoice with an order cannot be blocked', function (): void {
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
    $purchaseInvoice = ($this->makePurchaseInvoice)($order->getKey());

    BlockPurchaseInvoiceFile::assertValidationErrors(['id' => $purchaseInvoice->getKey()], 'id');

    expect(PurchaseInvoice::query()->whereKey($purchaseInvoice->getKey())->exists())->toBeTrue()
        ->and(BlockedFile::query()->count())->toBe(0);
});

function createOrderForBlockedFileTest(Contact $contact): Order
{
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);

    return Order::factory()->create([
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => test()->defaultLanguage->getKey(),
        'order_type_id' => OrderType::factory()->create()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'tenant_id' => test()->dbTenant->getKey(),
    ]);
}

test('a file of a read-only collection cannot be blocked', function (): void {
    $order = createOrderForBlockedFileTest($this->contact);
    $path = tempnam(sys_get_temp_dir(), 'invoice_') . '.png';
    imagepng(imagecreatetruecolor(1, 1), $path);
    $media = UploadMedia::make([
        'model_type' => morph_alias(Order::class),
        'model_id' => $order->getKey(),
        'media' => $path,
        'file_name' => 'invoice.png',
        'collection_name' => 'invoice',
    ])
        ->force()
        ->validate()
        ->execute();

    BlockFile::assertValidationErrors(['media_id' => $media->getKey()], 'media_id');

    expect(Media::query()->whereKey($media->getKey())->exists())->toBeTrue();
});

test('the file of a purchase invoice with an order cannot be blocked from a media list', function (): void {
    $order = createOrderForBlockedFileTest($this->contact);
    $purchaseInvoice = ($this->makePurchaseInvoice)($order->getKey());

    BlockFile::assertValidationErrors(['media_id' => $purchaseInvoice->media_id], 'media_id');

    expect(Media::query()->whereKey($purchaseInvoice->media_id)->exists())->toBeTrue();
});

test('a purchase invoice without file cannot be blocked', function (): void {
    $purchaseInvoice = PurchaseInvoice::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);

    BlockPurchaseInvoiceFile::assertValidationErrors(['id' => $purchaseInvoice->getKey()], 'id');

    expect(PurchaseInvoice::query()->whereKey($purchaseInvoice->getKey())->exists())->toBeTrue();
});

test('replacing a file with a blocked one reports a validation error', function (): void {
    $media = ($this->storeFile)('first content', morph_alias(Contact::class), $this->contact->getKey());
    $blocked = tempnam(sys_get_temp_dir(), 'logo_');
    file_put_contents($blocked, 'logo content');
    BlockedFile::query()->create(['hash' => md5('logo content'), 'file_name' => 'logo.txt']);

    expect(fn () => FluxErp\Actions\Media\ReplaceMedia::make([
        'id' => $media->getKey(),
        'media' => $blocked,
        'file_name' => 'logo.txt',
    ])->validate()->execute())->toThrow(Illuminate\Validation\ValidationException::class);
});

test('replacing a file with a blocked stream closes the stream', function (): void {
    $media = ($this->storeFile)('first content', morph_alias(Contact::class), $this->contact->getKey());
    BlockedFile::query()->create(['hash' => md5('logo content'), 'file_name' => 'logo.txt']);
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, 'logo content');
    rewind($stream);

    try {
        FluxErp\Actions\Media\ReplaceMedia::make([
            'id' => $media->getKey(),
            'media' => $stream,
            'media_type' => 'stream',
            'file_name' => 'logo.txt',
        ])->validate()->execute();
    } catch (Illuminate\Validation\ValidationException) {
    }

    expect(is_resource($stream))->toBeFalse();
});
