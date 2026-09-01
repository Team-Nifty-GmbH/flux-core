<?php

use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $file = UploadedFile::fake()->image('TestFile.png');

    $this->media = $this->user->addMedia($file)
        ->usingFileName($this->filename = Str::random() . '.png')
        ->toMediaCollection();
});

test('download media', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('media.{media}.{filename}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertOk()
        ->assertDownload();
});

test('download media with non ascii filename keeps the umlaut', function (): void {
    $media = $this->user->addMedia(UploadedFile::fake()->image('TestFile.png'))
        ->usingFileName('Develon_Dozer_Polen-Militär-1.png')
        ->toMediaCollection();

    $url = URL::signedRoute('media.show', [
        'media' => $media->getKey(),
        'download' => 1,
    ]);

    $disposition = $this->get($url)
        ->assertOk()
        ->headers->get('Content-Disposition');

    expect($disposition)
        ->toContain("filename*=utf-8''Develon_Dozer_Polen-Milit%C3%A4r-1.png")
        ->toContain('filename=Develon_Dozer_Polen-Militar-1.png');
});

test('download media media not found', function (): void {
    $this->media->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('media.{media}.{filename}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertNotFound();
});

test('download media no user', function (): void {
    $this->actingAsGuest();

    $this->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('download media without permission', function (): void {
    Permission::findOrCreate('media.{media}.{filename}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertForbidden();
});
