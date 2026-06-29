<?php

use FluxErp\Livewire\Task\Media;
use FluxErp\Models\Task;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('downloadCollection redirects to signed download route', function (): void {
    $task = Task::factory()->create();

    foreach (range(1, 3) as $i) {
        $task->addMedia(UploadedFile::fake()->image("photo{$i}.png", 100, 100))
            ->toMediaCollection('default');
    }

    Livewire::test(Media::class, ['modelId' => $task->getKey()])
        ->set('collection', 'default')
        ->call('downloadCollection', $task->uuid, 'default')
        ->assertRedirectContains('/media-collection-download/');
});
