<?php

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('user.private-storage.{path}.get', 'web'));

    $this->ownFolder = private_storage_folder($this->user->getMorphClass() . ':' . $this->user->getKey());
    $this->otherUser = User::factory()->create(['is_active' => true]);
    $this->otherFolder = private_storage_folder($this->otherUser->getMorphClass() . ':' . $this->otherUser->getKey());

    Storage::put('exports/' . $this->ownFolder . '/own.csv', 'own-export');
    Storage::put('documents/' . $this->ownFolder . '/own.pdf', 'own-document');
    Storage::put('exports/' . $this->otherFolder . '/foreign.csv', 'foreign-export');
    Storage::put('documents/' . $this->otherFolder . '/foreign.pdf', 'foreign-document');
    Storage::put('secret.txt', 'secret');
});

afterEach(function (): void {
    Storage::deleteDirectory('exports/' . $this->ownFolder);
    Storage::deleteDirectory('documents/' . $this->ownFolder);
    Storage::deleteDirectory('exports/' . $this->otherFolder);
    Storage::deleteDirectory('documents/' . $this->otherFolder);
    Storage::delete('secret.txt');
});

test('own export is downloadable', function (): void {
    $response = $this->get(route('private-storage', ['path' => 'exports/' . $this->ownFolder . '/own.csv']));

    $response->assertOk();
    expect(file_get_contents($response->baseResponse->getFile()->getPathname()))->toBe('own-export');
});

test('own document is downloadable', function (): void {
    $this->get(route('private-storage', ['path' => 'documents/' . $this->ownFolder . '/own.pdf']))
        ->assertOk();
});

test('export of another user is not served', function (): void {
    $this->get(route('private-storage', ['path' => 'exports/' . $this->otherFolder . '/foreign.csv']))
        ->assertNotFound();
});

test('document of another user is not served', function (): void {
    $this->get(route('private-storage', ['path' => 'documents/' . $this->otherFolder . '/foreign.pdf']))
        ->assertNotFound();
});

test('file outside the user folders is not served', function (): void {
    $this->get(route('private-storage', ['path' => 'secret.txt']))
        ->assertNotFound();
});

test('path traversal out of the own folder is not served', function (string $path): void {
    $this->get(route('private-storage', ['path' => sprintf($path, $this->ownFolder, $this->otherFolder)]))
        ->assertNotFound();
})->with([
    'into foreign export' => 'exports/%s/../%s/foreign.csv',
    'into storage root' => 'exports/%s/../../secret.txt',
    'own folder itself' => 'exports/%s/..',
    'backslash into foreign export' => 'exports/%s/..\\%s\\foreign.csv',
]);

test('guest is redirected to login', function (): void {
    $this->actingAsGuest();

    $this->get(route('private-storage', ['path' => 'exports/' . $this->ownFolder . '/own.csv']))
        ->assertRedirect(route('login'));
});

test('private storage is served below the user prefix', function (): void {
    expect(route('private-storage', ['path' => 'exports/' . $this->ownFolder . '/own.csv'], false))
        ->toBe('/user/private-storage/exports/' . $this->ownFolder . '/own.csv');
});

test('the migration moves the old private storage permission to the new route', function (): void {
    $role = Role::findOrCreate('private-storage-test', 'web');
    $role->givePermissionTo(Permission::findOrCreate('private-storage.{path}.get', 'web'));

    (require __DIR__ . '/../../../database/migrations/2026_10_26_000001_rename_private_storage_permission_in_permissions_table.php')->up();

    expect(Permission::query()->where('name', 'private-storage.{path}.get')->exists())->toBeFalse()
        ->and($role->fresh()->hasPermissionTo('user.private-storage.{path}.get', 'web'))->toBeTrue();
});
