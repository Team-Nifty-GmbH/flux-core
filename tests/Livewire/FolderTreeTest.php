<?php

use FluxErp\Livewire\Support\FolderTree;
use FluxErp\Models\Contact;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $permission = Permission::findOrCreate('action.media-folder.update', 'web');
    $this->user->givePermissionTo($permission);
});

test('renders successfully', function (): void {
    Livewire::test(FolderTreeTestClass::class)
        ->assertOk();
});

test('can move media from one folder to another', function (): void {
    Storage::fake('local');

    $sourceFolder = MediaFolder::create([
        'name' => 'Source Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $targetFolder = MediaFolder::create([
        'name' => 'Target Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $media = $sourceFolder
        ->addMedia(UploadedFile::fake()->image('test.jpg'))
        ->toMediaCollection('files');

    $mediaId = $media->getKey();
    $fileName = $media->file_name;

    $subject = [
        'id' => $mediaId,
        'name' => $media->name,
        'file_name' => $fileName,
        'collection_name' => $media->collection_name,
    ];

    $target = [
        'id' => $targetFolder->getKey(),
        'name' => $targetFolder->name,
        'slug' => $targetFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, null)
        ->assertReturned(true);

    $movedMedia = Media::query()
        ->where('model_type', morph_alias(MediaFolder::class))
        ->where('model_id', $targetFolder->getKey())
        ->where('file_name', $fileName)
        ->first();

    expect($movedMedia)->not->toBeNull()
        ->and($movedMedia->model_type)->toBe(morph_alias(MediaFolder::class))
        ->and($movedMedia->model_id)->toBe($targetFolder->getKey());
});

test('can move media from parent model to folder', function (): void {
    Storage::fake('local');

    $targetFolder = MediaFolder::create([
        'name' => 'Target Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $media = $this->contact
        ->addMedia(UploadedFile::fake()->image('test.jpg'))
        ->toMediaCollection('files');

    $fileName = $media->file_name;

    $subject = [
        'id' => $media->getKey(),
        'name' => $media->name,
        'file_name' => $fileName,
        'collection_name' => $media->collection_name,
    ];

    $target = [
        'id' => $targetFolder->getKey(),
        'name' => $targetFolder->name,
        'slug' => $targetFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, null)
        ->assertReturned(true);

    $movedMedia = Media::query()
        ->where('model_type', morph_alias(MediaFolder::class))
        ->where('model_id', $targetFolder->getKey())
        ->where('file_name', $fileName)
        ->first();

    expect($movedMedia)->not->toBeNull()
        ->and($movedMedia->model_type)->toBe(morph_alias(MediaFolder::class))
        ->and($movedMedia->model_id)->toBe($targetFolder->getKey());
});

test('can move folder to another folder', function (): void {
    $parentFolder = MediaFolder::create([
        'name' => 'Parent Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);
    $this->contact->mediaFolders()->attach($parentFolder->getKey());

    $childFolder = MediaFolder::create([
        'name' => 'Child Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);
    $this->contact->mediaFolders()->attach($childFolder->getKey());

    $subject = [
        'id' => $childFolder->getKey(),
        'name' => $childFolder->name,
        'slug' => $childFolder->slug,
    ];

    $target = [
        'id' => $parentFolder->getKey(),
        'name' => $parentFolder->name,
        'slug' => $parentFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, $childFolder->slug, $parentFolder->slug)
        ->assertReturned(true);

    $childFolder->refresh();

    expect($childFolder->parent_id)->toBe($parentFolder->getKey());
});

test('cannot move to readonly folder', function (): void {
    Storage::fake('local');

    $sourceFolder = MediaFolder::create([
        'name' => 'Source Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $readonlyFolder = MediaFolder::create([
        'name' => 'Readonly Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
        'is_readonly' => true,
    ]);

    $media = $sourceFolder
        ->addMedia(UploadedFile::fake()->image('test.jpg'))
        ->toMediaCollection('files');

    $subject = [
        'id' => $media->getKey(),
        'name' => $media->name,
        'file_name' => $media->file_name,
        'collection_name' => $media->collection_name,
    ];

    $target = [
        'id' => $readonlyFolder->getKey(),
        'name' => $readonlyFolder->name,
        'slug' => $readonlyFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, null)
        ->assertReturned(false);

    $media->refresh();

    expect($media->model_id)->toBe($sourceFolder->getKey());
});

test('cannot move when component is readonly', function (): void {
    Storage::fake('local');

    $sourceFolder = MediaFolder::create([
        'name' => 'Source Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $targetFolder = MediaFolder::create([
        'name' => 'Target Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $media = $sourceFolder
        ->addMedia(UploadedFile::fake()->image('test.jpg'))
        ->toMediaCollection('files');

    $subject = [
        'id' => $media->getKey(),
        'name' => $media->name,
        'file_name' => $media->file_name,
        'collection_name' => $media->collection_name,
    ];

    $target = [
        'id' => $targetFolder->getKey(),
        'name' => $targetFolder->name,
        'slug' => $targetFolder->slug,
    ];

    Livewire::test(FolderTreeReadonlyTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, null)
        ->assertReturned(false);

    $media->refresh();

    expect($media->model_id)->toBe($sourceFolder->getKey());
});

test('returns false when media not found', function (): void {
    $targetFolder = MediaFolder::create([
        'name' => 'Target Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $subject = [
        'id' => 999999,
        'name' => 'nonexistent.jpg',
        'file_name' => 'nonexistent.jpg',
        'collection_name' => 'files',
    ];

    $target = [
        'id' => $targetFolder->getKey(),
        'name' => $targetFolder->name,
        'slug' => $targetFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, 'files', $targetFolder->slug)
        ->assertReturned(false);
});

test('returns false when subject path cannot be determined', function (): void {
    $targetFolder = MediaFolder::create([
        'name' => 'Target Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $subject = [
        'id' => 1,
        'name' => 'test',
    ];

    $target = [
        'id' => $targetFolder->getKey(),
        'name' => $targetFolder->name,
        'slug' => $targetFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, $targetFolder->slug)
        ->assertReturned(false);
});

test('returns false when target path cannot be determined', function (): void {
    Storage::fake('local');

    $sourceFolder = MediaFolder::create([
        'name' => 'Source Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $media = $sourceFolder
        ->addMedia(UploadedFile::fake()->image('test.jpg'))
        ->toMediaCollection('files');

    $subject = [
        'id' => $media->getKey(),
        'name' => $media->name,
        'file_name' => $media->file_name,
        'collection_name' => $media->collection_name,
    ];

    $target = [
        'id' => 'string-id',
        'name' => 'Some Target',
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, null)
        ->assertReturned(false);
});

test('moving media with dot in name does not create subfolder', function (): void {
    Storage::fake('local');

    $targetFolder = MediaFolder::create([
        'name' => 'Target Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);
    $this->contact->mediaFolders()->attach($targetFolder->getKey());

    // Create media with a dot in the name (like "document.v2")
    $media = $this->contact
        ->addMedia(UploadedFile::fake()->image('test.document.v2.jpg'))
        ->toMediaCollection('files');

    $fileName = $media->file_name;

    $subject = [
        'id' => $media->getKey(),
        'name' => $media->name, // This would be "test.document.v2"
        'file_name' => $fileName,
        'collection_name' => $media->collection_name,
    ];

    $target = [
        'id' => $targetFolder->getKey(),
        'name' => $targetFolder->name,
        'slug' => $targetFolder->slug,
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, null, null)
        ->assertReturned(true);

    // Find the moved media
    $movedMedia = Media::query()
        ->where('model_type', morph_alias(MediaFolder::class))
        ->where('model_id', $targetFolder->getKey())
        ->where('file_name', $fileName)
        ->first();

    expect($movedMedia)->not->toBeNull();
    // Collection name should be the folder slug, NOT containing the file name with dots
    expect($movedMedia->collection_name)->toBe($targetFolder->slug)
        ->and($movedMedia->collection_name)->not->toContain('document');
});

test('cannot move folder to collection', function (): void {
    $folder = MediaFolder::create([
        'name' => 'Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);

    $subject = [
        'id' => $folder->getKey(),
        'name' => $folder->name,
        'slug' => $folder->slug,
    ];

    $target = [
        'id' => 'collection-id',
        'name' => 'Some Collection',
        'collection_name' => 'files',
    ];

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('moveItem', $subject, $target, $folder->slug, 'files')
        ->assertReturned(false);
});

test('can delete collection with path string', function (): void {
    Storage::fake('local');

    // Give user permission to delete media collection
    $permission = Permission::findOrCreate('action.media-collection.delete', 'web');
    $this->user->givePermissionTo($permission);

    // Create media in a nested collection
    $media = $this->contact
        ->addMedia(UploadedFile::fake()->image('test.jpg'))
        ->toMediaCollection('attachments.subfolder');

    // Verify media exists
    expect(Media::where('collection_name', 'attachments.subfolder')->exists())->toBeTrue();

    // Delete using path string (as returned by getNodePath in JS)
    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('deleteCollection', 'virtual-folder-id', 'attachments.subfolder')
        ->assertReturned(true);

    // Verify media is deleted
    expect(Media::where('collection_name', 'attachments.subfolder')->exists())->toBeFalse();
});

test('can delete real media folder', function (): void {
    Storage::fake('local');

    // Give user permission to delete media folder
    $permission = Permission::findOrCreate('action.media-folder.delete', 'web');
    $this->user->givePermissionTo($permission);

    $folder = MediaFolder::create([
        'name' => 'Test Folder',
        'model_type' => morph_alias(Contact::class),
        'model_id' => $this->contact->getKey(),
    ]);
    $this->contact->mediaFolders()->attach($folder->getKey());

    // Verify folder exists
    expect(MediaFolder::whereKey($folder->getKey())->exists())->toBeTrue();

    Livewire::test(FolderTreeTestClass::class, ['modelId' => $this->contact->getKey()])
        ->call('deleteCollection', $folder->getKey(), null)
        ->assertReturned(true);

    // Verify folder is deleted
    expect(MediaFolder::whereKey($folder->getKey())->exists())->toBeFalse();
});

class FolderTreeTestClass extends FolderTree
{
    protected string $modelType = Contact::class;
}

class FolderTreeReadonlyTestClass extends FolderTree
{
    public bool $isReadonly = true;

    protected string $modelType = Contact::class;
}
