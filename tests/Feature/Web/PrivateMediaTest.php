<?php

use FluxErp\Models\Address;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $file = UploadedFile::fake()->image('original.png', 800, 600);

    /** @var Media $media */
    $this->media = $this->user->addMedia($file)
        ->usingFileName($this->filename = Str::random() . '.png')
        ->toMediaCollection();

    // Pretend a thumb_400x400 has been generated. The actual file is created below
    // so the controller can stream a real conversion file.
    $this->media->generated_conversions = ['thumb_400x400' => true];
    $this->media->save();

    $conversionPath = $this->media->getPath('thumb_400x400');
    @mkdir(dirname($conversionPath), recursive: true);
    file_put_contents($conversionPath, 'fake-thumb-bytes');
});

test('signed route without conversion query returns the original', function (): void {
    $url = URL::temporarySignedRoute('media.private', now()->addMinutes(5), [
        'media' => $this->media->getKey(),
        'filename' => $this->filename,
    ]);

    $this->get($url)->assertOk();
});

test('signed route with conversion query serves the conversion file', function (): void {
    $url = URL::temporarySignedRoute('media.private', now()->addMinutes(5), [
        'media' => $this->media->getKey(),
        'filename' => $this->filename,
        'conversion' => 'thumb_400x400',
    ]);

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('Content-Disposition');
    expect($response->streamedContent())->toBe('fake-thumb-bytes');
});

test('guest with a valid signature is served without authentication', function (): void {
    $this->actingAsGuest();

    $url = URL::temporarySignedRoute('media.private', now()->addMinutes(5), [
        'media' => $this->media->getKey(),
        'filename' => $this->filename,
    ]);

    $this->get($url)->assertOk();
});

test('guest without a valid signature is rejected', function (): void {
    $this->actingAsGuest();

    $this->get('/media-private/' . $this->media->getKey() . '/' . $this->filename)
        ->assertStatus(403);
});

test('authenticated user is served without a signature', function (): void {
    $this->get('/media-private/' . $this->media->getKey() . '/' . $this->filename)
        ->assertOk();
});

test('authenticated non-user is rejected without a signature', function (): void {
    $this->be(new Address(), 'web');

    $this->get('/media-private/' . $this->media->getKey() . '/' . $this->filename)
        ->assertStatus(403);
});

test('authenticated user is denied when a global scope hides the media', function (): void {
    Media::addGlobalScope('test_hide_media', function (Builder $query): void {
        $query->whereRaw('1 = 0');
    });

    try {
        $this->get('/media-private/' . $this->media->getKey() . '/' . $this->filename)
            ->assertNotFound();
    } finally {
        Model::clearBootedModels();
    }
});

test('signed route with conversion query 404s when the conversion is not generated', function (): void {
    $this->media->generated_conversions = [];
    $this->media->save();

    $url = URL::temporarySignedRoute('media.private', now()->addMinutes(5), [
        'media' => $this->media->getKey(),
        'filename' => $this->filename,
        'conversion' => 'thumb_400x400',
    ]);

    $this->get($url)->assertNotFound();
});

test('signed route with conversion query 404s when the conversion file is missing on disk', function (): void {
    @unlink($this->media->getPath('thumb_400x400'));

    $url = URL::temporarySignedRoute('media.private', now()->addMinutes(5), [
        'media' => $this->media->getKey(),
        'filename' => $this->filename,
        'conversion' => 'thumb_400x400',
    ]);

    $this->get($url)->assertNotFound();
});
