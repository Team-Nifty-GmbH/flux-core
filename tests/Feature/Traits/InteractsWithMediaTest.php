<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
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
