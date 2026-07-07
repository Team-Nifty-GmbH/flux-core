<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;

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

test('getMediaAsTree orders files by order column', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);

    $mediaA = Media::factory()->create([
        'model_type' => $address->getMorphClass(),
        'model_id' => $address->getKey(),
        'collection_name' => 'files',
        'name' => 'a.png',
        'file_name' => 'a.png',
        'order_column' => 2,
    ]);

    $mediaB = Media::factory()->create([
        'model_type' => $address->getMorphClass(),
        'model_id' => $address->getKey(),
        'collection_name' => 'files',
        'name' => 'b.png',
        'file_name' => 'b.png',
        'order_column' => 1,
    ]);

    $tree = $address->getMediaAsTree();

    $files = collect($tree)->firstWhere('slug', 'files');
    expect($files)->not->toBeNull();

    expect(data_get($files, 'children.*.id'))->toBe([
        $mediaB->getKey(),
        $mediaA->getKey(),
    ]);
});
