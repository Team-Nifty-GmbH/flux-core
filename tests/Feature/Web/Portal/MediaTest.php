<?php

use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $file = UploadedFile::fake()->image('TestFile.png');

    $this->media = $this->address->contact->addMedia($file)
        ->usingFileName($this->filename = Str::random() . '.png')
        ->toMediaCollection();
});

test('download media', function (): void {
    $this->address->givePermissionTo(
        Permission::findOrCreate('media.{media}.{filename}.get', 'address')
    );

    $this->actingAs($this->address, 'address')
        ->get(config('flux.portal_domain') . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertOk()
        ->assertDownload();
});

test('download media media not found', function (): void {
    $this->media->delete();

    $this->address->givePermissionTo(
        Permission::findOrCreate('media.{media}.{filename}.get', 'address')
    );

    $this->actingAs($this->address, 'address')
        ->get(config('flux.portal_domain') . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertNotFound();
});

test('download media no user', function (): void {
    $this->actingAsGuest();

    $this->get(config('flux.portal_domain') . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('download media without permission', function (): void {
    Permission::findOrCreate('media.{media}.{filename}.get', 'address');

    $this->actingAs($this->address, 'address')
        ->get(config('flux.portal_domain') . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertForbidden();
});
