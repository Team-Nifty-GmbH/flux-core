<?php

use FluxErp\Livewire\Task\Media as MediaComponent;
use FluxErp\Models\Media;
use FluxErp\Models\Task;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Livewire\Livewire;

beforeEach(function (): void {
    // A disk that stores files locally but reports a non-local (relative) path the
    // way an S3-compatible disk does: getRootOfDisk() resolves to '', so
    // $media->getPath() is a relative string and file_exists() on it is always
    // false even though the file is present on the disk.
    $root = sys_get_temp_dir() . '/flux-remote-' . Str::random(8);
    $adapter = new LocalFilesystemAdapter($root);

    $disk = new class(new Flysystem($adapter), $adapter, ['root' => $root]) extends FilesystemAdapter
    {
        public function path($path): string
        {
            return ltrim((string) $path, '/');
        }
    };

    Storage::set('remote', $disk);
});

test('download redirects for a file on a remote disk where the local path does not exist', function (): void {
    $task = Task::factory()->create();

    $media = Media::query()->forceCreate([
        'model_type' => morph_alias(Task::class),
        'model_id' => $task->getKey(),
        'uuid' => (string) Str::uuid(),
        'collection_name' => 'default',
        'name' => 'sample',
        'file_name' => 'sample.xlsx',
        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'disk' => 'remote',
        'conversions_disk' => 'remote',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
    ]);

    Storage::disk('remote')->put($media->getPathRelativeToRoot(), 'content');

    // bug trigger: the local path check fails while the file is present on the disk
    expect(file_exists($media->getPath()))->toBeFalse();
    expect(Storage::disk('remote')->exists($media->getPathRelativeToRoot()))->toBeTrue();

    Livewire::test(MediaComponent::class, ['modelId' => $task->getKey()])
        ->call('download', $media->getKey())
        ->assertRedirect();
});
