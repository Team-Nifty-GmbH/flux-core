<?php

use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Queue::fake();
    Storage::fake(config('media-library.disk_name'));

    $contact = Contact::factory()->create();

    $this->order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create()->getKey(),
        'address_invoice_id' => Address::factory()->create(['contact_id' => $contact->getKey()])->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);
});

function replaceMediaPdf(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n" . $name);
}

function replaceMediaSeed(Model $model, string $collection, string $name = 'old.pdf'): Media
{
    return $model->addMedia(replaceMediaPdf($name))
        ->usingFileName($name)
        ->toMediaCollection($collection);
}

function replaceMediaExists(Media $media): bool
{
    return Storage::disk($media->disk)->exists($media->getPathRelativeToRoot());
}

test('replacing media of a single file collection deletes the old media exactly once', function (): void {
    $old = replaceMediaSeed($this->order, 'invoice');

    $deleted = [];
    Event::listen(
        'eloquent.deleted: ' . Media::class,
        function (Media $media) use (&$deleted): void {
            $deleted[] = $media->getKey();
        }
    );

    $new = ReplaceMedia::make([
        'id' => $old->getKey(),
        'media' => replaceMediaPdf('new.pdf'),
    ])
        ->force()
        ->validate()
        ->execute();

    expect($deleted)->toBe([$old->getKey()])
        ->and(Media::query()->whereKey($old->getKey())->exists())->toBeFalse()
        ->and(replaceMediaExists($old))->toBeFalse()
        ->and(replaceMediaExists($new))->toBeTrue()
        ->and($this->order->fresh()->getMedia('invoice')->pluck('id')->all())->toBe([$new->getKey()])
        ->and($new->file_name)->toBe('new.pdf');
});

test('replacing media of a single file collection keeps the records pointing at it', function (): void {
    $purchaseInvoice = PurchaseInvoice::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
    $old = replaceMediaSeed($purchaseInvoice, 'purchase_invoice');
    $purchaseInvoice->update(['media_id' => $old->getKey()]);

    $new = ReplaceMedia::make([
        'id' => $old->getKey(),
        'media' => replaceMediaPdf('new.pdf'),
    ])
        ->force()
        ->validate()
        ->execute();

    expect($purchaseInvoice->fresh()->media_id)->toBe($new->getKey());
});

test('a failing replace keeps the old file of a single file collection', function (): void {
    $old = replaceMediaSeed($this->order, 'invoice');

    $action = new class(['id' => $old->getKey(), 'media' => replaceMediaPdf('new.pdf')]) extends ReplaceMedia
    {
        protected function replaceOldMediaOnModels(int|string $oldMediaId, Model $newMedia): void
        {
            throw new RuntimeException('failed');
        }
    };

    expect(
        fn () => $action->force()
            ->validate()
            ->execute()
    )
        ->toThrow(RuntimeException::class, 'failed')
        ->and(replaceMediaExists($old))->toBeTrue();
});

test('replacing media of a collection without single file only touches the replaced media', function (): void {
    $old = replaceMediaSeed($this->order, 'documents');
    $sibling = replaceMediaSeed($this->order, 'documents', 'sibling.pdf');

    $new = ReplaceMedia::make([
        'id' => $old->getKey(),
        'media' => replaceMediaPdf('new.pdf'),
    ])
        ->validate()
        ->execute();

    expect(Media::query()->whereKey($old->getKey())->exists())->toBeFalse()
        ->and(replaceMediaExists($old))->toBeFalse()
        ->and(replaceMediaExists($new))->toBeTrue()
        ->and(replaceMediaExists($sibling))->toBeTrue()
        ->and($this->order->fresh()->getMedia('documents')->pluck('id')->sort()->values()->all())
        ->toBe([$sibling->getKey(), $new->getKey()])
        ->and($new->collection_name)->toBe('documents');
});

test('deleting media that is already gone does not fail', function (): void {
    expect(
        DeleteMedia::make(['id' => 0])
            ->execute()
    )
        ->toBeNull();
});
