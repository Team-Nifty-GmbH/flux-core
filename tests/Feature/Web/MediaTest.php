<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
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
        ->assertStatus(200)
        ->assertDownload();
});

test('download media media not found', function (): void {
    $this->media->delete();

    $this->user->givePermissionTo(Permission::findOrCreate('media.{media}.{filename}.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(404);
});

test('download media no user', function (): void {
    $this->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('download media without permission', function (): void {
    Permission::findOrCreate('media.{media}.{filename}.get', 'web');

    $this->actingAs($this->user, 'web')->get('/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(403);
});
