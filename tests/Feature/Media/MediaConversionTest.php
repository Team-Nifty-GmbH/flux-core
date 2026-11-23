<?php

use FluxErp\Models\Contact;
use FluxErp\Models\Media;

beforeEach(function (): void {
    config(['media-library.queue_conversions_by_default' => false]);

    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $this->addFile = function (string $fileName, Closure $write): Media {
        $path = sys_get_temp_dir() . '/' . uniqid() . '_' . $fileName;
        $write($path);

        return $this->contact->addMedia($path)->toMediaCollection('default');
    };

    $this->png = fn (int $width, int $height): Closure => function (string $path) use ($width, $height): void {
        imagepng(imagecreatetruecolor($width, $height), $path);
    };
});

test('thumbnails of an image with an extreme aspect ratio stay within their bounds', function (): void {
    $media = ($this->addFile)('banner.png', ($this->png)(1600, 2));

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($media->hasGeneratedConversion('thumb_800x800'))->toBeTrue();

    [$width, $height] = getimagesize($media->getPath('thumb'));
    expect($width)->toBeLessThanOrEqual(100)
        ->and($height)->toBeLessThanOrEqual(100);

    [$width, $height] = getimagesize($media->getPath('thumb_800x800'));
    expect($width)->toBeLessThanOrEqual(800)
        ->and($height)->toBeLessThanOrEqual(800);
});

test('thumbnails of a tall narrow image stay within their bounds', function (): void {
    $media = ($this->addFile)('strip.png', ($this->png)(2, 1600));

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();

    [$width, $height] = getimagesize($media->getPath('thumb'));
    expect($width)->toBeLessThanOrEqual(100)
        ->and($height)->toBeLessThanOrEqual(100);
});

test('an image above the pixel limit is stored without thumbnails', function (): void {
    config(['flux.media.max_image_pixels' => 100]);

    $media = ($this->addFile)('huge.png', ($this->png)(20, 20));

    expect($media->exists)->toBeTrue()
        ->and(file_exists($media->getPath()))->toBeTrue()
        ->and($media->hasGeneratedConversion('thumb'))->toBeFalse();
});

test('a pdf imagick cannot read is stored without thumbnails', function (): void {
    $media = ($this->addFile)('broken.pdf', function (string $path): void {
        file_put_contents($path, "%PDF-1.4\nnot really a pdf");
    });

    expect($media->exists)->toBeTrue()
        ->and(file_exists($media->getPath()))->toBeTrue()
        ->and($media->hasGeneratedConversion('thumb'))->toBeFalse();
});
