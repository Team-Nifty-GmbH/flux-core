<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $file = UploadedFile::fake()->image('TestFile.png');

    $this->media = $this->user->contact->addMedia($file)
        ->usingFileName($this->filename = Str::random() . '.png')
        ->toMediaCollection();
});

test('download media', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('media.{media}.{filename}.get', 'address')
    );

    $this->actingAs($this->user, 'address')
        ->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(200)
        ->assertDownload();
});

test('download media media not found', function (): void {
    $this->media->delete();

    $this->user->givePermissionTo(
        Permission::findOrCreate('media.{media}.{filename}.get', 'address')
    );

    $this->actingAs($this->user, 'address')
        ->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(404);
});

test('download media no user', function (): void {
    $this->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('download media without permission', function (): void {
    Permission::findOrCreate('media.{media}.{filename}.get', 'address');

    $this->actingAs($this->user, 'address')
        ->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
        ->assertStatus(403);
});
