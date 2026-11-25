<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use FluxErp\Models\Task;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('getMediaAsTree keeps all sibling branches under a shared dotted prefix', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    foreach (
        [
            'Berater.Nextcloud.Alpha',
            'Berater.Nextcloud.Beta',
            'Berater.Nextcloud.Gamma',
        ] as $collection
    ) {
        Media::factory()->create([
            'model_type' => $address->getMorphClass(),
            'model_id' => $address->getKey(),
            'collection_name' => $collection,
        ]);
    }

    $tree = $address->getMediaAsTree();

    $berater = collect($tree)->firstWhere('slug', 'Berater');
    expect($berater)->not->toBeNull();

    $nextcloud = collect(data_get($berater, 'children'))->firstWhere('slug', 'Berater.Nextcloud');
    expect($nextcloud)->not->toBeNull();

    $childSlugs = collect(data_get($nextcloud, 'children'))
        ->pluck('slug')
        ->sort()
        ->values()
        ->all();

    expect($childSlugs)->toBe([
        'Berater.Nextcloud.Alpha',
        'Berater.Nextcloud.Beta',
        'Berater.Nextcloud.Gamma',
    ]);
});

test('deleting a media record removes its file from the disk', function (): void {
    Storage::fake(config('media-library.disk_name'));

    $contact = Contact::factory()->create();

    $media = $contact
        ->addMedia(UploadedFile::fake()->create('contract.pdf', 12))
        ->toMediaCollection();

    $path = $media->id . '/' . $media->file_name;

    Storage::disk($media->disk)->assertExists($path);

    $media->delete();

    Storage::disk($media->disk)->assertMissing($path);
});

test('conversions of an extremely flat image stay within their bounding box', function (): void {
    Storage::fake(config('media-library.disk_name'));
    config(['media-library.queue_conversions_by_default' => false]);

    $media = Contact::factory()->create()
        ->addMedia(UploadedFile::fake()->image('divider.jpg', 588, 1))
        ->toMediaCollection();

    expect(array_slice(getimagesize($media->getPath('preview')), 0, 2))->toBe([150, 1])
        ->and(array_slice(getimagesize($media->getPath('thumb')), 0, 2))->toBe([100, 1])
        ->and(array_slice(getimagesize($media->getPath('thumb_280x280')), 0, 2))->toBe([280, 1])
        ->and(array_slice(getimagesize($media->getPath('thumb_400x400')), 0, 2))->toBe([400, 1])
        ->and(array_slice(getimagesize($media->getPath('thumb_800x800')), 0, 2))->toBe([588, 1]);
});

test('conversions scale a normal image down into their bounding box', function (): void {
    Storage::fake(config('media-library.disk_name'));
    config(['media-library.queue_conversions_by_default' => false]);

    $media = Contact::factory()->create()
        ->addMedia(UploadedFile::fake()->image('photo.jpg', 1000, 500))
        ->toMediaCollection();

    expect(array_slice(getimagesize($media->getPath('preview')), 0, 2))->toBe([150, 75])
        ->and(array_slice(getimagesize($media->getPath('thumb')), 0, 2))->toBe([100, 50])
        ->and(array_slice(getimagesize($media->getPath('thumb_280x280')), 0, 2))->toBe([280, 140])
        ->and(array_slice(getimagesize($media->getPath('thumb_400x400')), 0, 2))->toBe([400, 200])
        ->and(array_slice(getimagesize($media->getPath('thumb_800x800')), 0, 2))->toBe([800, 400]);
});

test('task thumbnail of an extremely flat image stays within its bounding box', function (): void {
    Storage::fake(config('media-library.disk_name'));

    $media = Task::factory()->create()
        ->addMedia(UploadedFile::fake()->image('divider.jpg', 588, 1))
        ->toMediaCollection('files');

    expect(array_slice(getimagesize($media->getPath('thumb')), 0, 2))->toBe([368, 1]);
});
