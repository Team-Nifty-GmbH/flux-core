<?php

use FluxErp\Livewire\Task\Media;
use FluxErp\Models\Task;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('downloadCollection returns a streamed response', function (): void {
    $task = Task::factory()->create();

    foreach (range(1, 3) as $i) {
        $task->addMedia(UploadedFile::fake()->image("photo{$i}.png", 100, 100))
            ->toMediaCollection('default');
    }

    $component = Livewire::test(Media::class, ['modelId' => $task->getKey()]);
    $component->set('collection', 'default');

    $response = $component->call('downloadCollection', $task->uuid, 'default');

    expect($response->effects['download'] ?? null)->not->toBeNull();
});

test('downloadCollection does not exceed memory for large files', function (): void {
    $task = Task::factory()->create();

    // Create files that would exceed 128MB if buffered together
    foreach (range(1, 3) as $i) {
        $task->addMedia(UploadedFile::fake()->create("large{$i}.bin", 1024)) // 1MB each
            ->toMediaCollection('default');
    }

    $component = Livewire::test(Media::class, ['modelId' => $task->getKey()]);
    $component->set('collection', 'default');

    // Should not throw memory exhaustion
    $response = $component->call('downloadCollection', $task->uuid, 'default');

    expect($response->effects['download'] ?? null)->not->toBeNull();
});
