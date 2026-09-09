<?php

use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use Illuminate\Support\Facades\Storage;

function mediaWithFileOnDisk(array $attributes = []): Media
{
    $contact = Contact::factory()->create();

    $media = Media::factory()->create(array_merge([
        'model_type' => $contact->getMorphClass(),
        'model_id' => $contact->getKey(),
        'collection_name' => 'documents',
        'file_name' => 'old-name.pdf',
        'disk' => 'public',
        'conversions_disk' => 'public',
    ], $attributes));

    Storage::disk('public')->put($media->getPathRelativeToRoot(), 'content');

    return $media;
}

test('renaming a media moves the file to the new name', function (): void {
    Storage::fake('public');

    $media = mediaWithFileOnDisk();
    $oldPath = $media->getPathRelativeToRoot();

    UpdateMedia::make(['id' => $media->getKey(), 'file_name' => 'new-name'])
        ->validate()
        ->execute();

    $media->refresh();

    expect($media->file_name)->toBe('new-name.pdf')
        ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot()))->toBeTrue()
        ->and(Storage::disk('public')->exists($oldPath))->toBeFalse();
});

test('renaming a media moves its generated conversions too', function (): void {
    Storage::fake('public');

    $media = mediaWithFileOnDisk([
        'file_name' => 'old-name.jpg',
        'generated_conversions' => ['preview' => true],
    ]);

    Storage::disk('public')->put($media->getPathRelativeToRoot('preview'), 'content');
    $oldConversionPath = $media->getPathRelativeToRoot('preview');

    UpdateMedia::make(['id' => $media->getKey(), 'file_name' => 'new-name'])
        ->validate()
        ->execute();

    $media->refresh();

    expect(Storage::disk('public')->exists($media->getPathRelativeToRoot('preview')))->toBeTrue()
        ->and(Storage::disk('public')->exists($oldConversionPath))->toBeFalse();
});

test('renaming a media sanitizes the name the same way an upload does', function (): void {
    Storage::fake('public');

    $media = mediaWithFileOnDisk();

    UpdateMedia::make(['id' => $media->getKey(), 'file_name' => 'Neugieriger Besucher der Lodge'])
        ->validate()
        ->execute();

    $media->refresh();

    expect($media->file_name)->toBe('Neugieriger-Besucher-der-Lodge.pdf')
        ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot()))->toBeTrue();
});
