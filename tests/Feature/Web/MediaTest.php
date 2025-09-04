<?php

use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
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
