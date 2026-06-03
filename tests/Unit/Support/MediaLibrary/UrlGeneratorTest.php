<?php

use FluxErp\Models\Media;
use FluxErp\Support\MediaLibrary\UrlGenerator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

function makeGenerator(Media $media, ?Conversion $conversion = null): UrlGenerator
{
    $generator = app(UrlGenerator::class);
    $generator->setMedia($media);
    $generator->setPathGenerator(new DefaultPathGenerator());

    if ($conversion !== null) {
        $generator->setConversion($conversion);
    }

    return $generator;
}

function makeMediaOnDisk(string $disk, string $conversionsDisk, string $thumbnailKey = 'thumb_400x400'): Media
{
    // We can't rely on toMediaCollection() because it has side effects (writing actual files).
    // For URL generation we only need a valid Media instance whose attributes drive the URL builder.
    $media = Media::query()->forceCreate([
        'model_type' => 'user',
        'model_id' => test()->user->getKey(),
        'uuid' => (string) Str::uuid(),
        'collection_name' => 'default',
        'name' => 'sample',
        'file_name' => 'sample.pdf',
        'mime_type' => 'application/pdf',
        'disk' => $disk,
        'conversions_disk' => $conversionsDisk,
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => json_encode([$thumbnailKey => true]),
        'responsive_images' => '[]',
    ]);

    return $media;
}

function makeThumbConversion(string $name = 'thumb_400x400'): Conversion
{
    return tap(new Conversion($name))
        ->width(400)
        ->height(400)
        ->keepOriginalImageFormat();
}

test('serves originals on the private disk through the signed media.private route', function (): void {
    $media = makeMediaOnDisk(disk: 'hetzner', conversionsDisk: 'public');

    $url = makeGenerator($media)->getUrl();

    expect($url)->toContain('/media-private/' . $media->getKey() . '/sample.pdf');
    expect($url)->toContain('signature=');
    expect($url)->not->toContain('conversion=');
});

test('serves conversions through the public disk URL when their disk is public', function (): void {
    // VVO's actual layout: originals on hetzner, derivatives written to the local public disk.
    // The generated thumb must be addressable via /storage/... — not signed-rerouted to the original.
    $media = makeMediaOnDisk(disk: 'hetzner', conversionsDisk: 'public');
    $conversion = makeThumbConversion();

    $url = makeGenerator($media, $conversion)->getUrl();

    expect($url)->toContain('/storage/' . $media->getKey() . '/conversions/sample-thumb_400x400.jpg');
    expect($url)->not->toContain('/media-private/');
    expect($url)->not->toContain('signature=');
});

test('signs the media.private route with a conversion parameter when the conversions disk is private', function (): void {
    $media = makeMediaOnDisk(disk: 'hetzner', conversionsDisk: 'hetzner');
    $conversion = makeThumbConversion();

    $url = makeGenerator($media, $conversion)->getUrl();

    expect($url)->toContain('/media-private/' . $media->getKey() . '/sample.pdf');
    expect($url)->toContain('conversion=thumb_400x400');
    expect($url)->toContain('signature=');
});

test('falls back to the Spatie default when both disks are public', function (): void {
    $media = makeMediaOnDisk(disk: 'public', conversionsDisk: 'public');

    expect(makeGenerator($media)->getUrl())
        ->toContain('/storage/' . $media->getKey() . '/sample.pdf')
        ->not->toContain('/media-private/');

    expect(makeGenerator($media, makeThumbConversion())->getUrl())
        ->toContain('/storage/' . $media->getKey() . '/conversions/sample-thumb_400x400.jpg')
        ->not->toContain('/media-private/');
});
