<?php

use FluxErp\Livewire\DataTables\MediaList;
use FluxErp\Models\Media;
use FluxErp\Models\Task;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MediaList::class)
        ->assertOk();
});

test('download redirects to the streamed media URL instead of dehydrating bytes through livewire', function (): void {
    Storage::fake('local');

    $task = Task::factory()->create(['responsible_user_id' => $this->user->getKey()]);
    $media = Media::factory()->create([
        'model_type' => $task->getMorphClass(),
        'model_id' => $task->getKey(),
        'disk' => 'local',
    ]);
    Storage::disk('local')->put($media->getPathRelativeToRoot(), 'fake-bytes');

    $component = Livewire::actingAs($this->user)
        ->test(MediaList::class)
        ->call('download', $media->getKey())
        ->assertOk()
        ->assertRedirect();

    // SupportFileDownloads injects a 'download' effect only when the action
    // returns a BinaryFileResponse / StreamedResponse. Its absence is the
    // observable proof the file is delivered out-of-band by a streamed
    // route, not buffered through the Livewire payload.
    expect($component->effects)->not->toHaveKey('download');
});

test('downloadCollection redirects to a signed media-collection route instead of dehydrating the zip', function (): void {
    Storage::fake('local');

    $task = Task::factory()->create(['responsible_user_id' => $this->user->getKey()]);
    foreach (range(1, 3) as $i) {
        Media::factory()->create([
            'model_type' => $task->getMorphClass(),
            'model_id' => $task->getKey(),
            'collection_name' => 'attachments',
            'disk' => 'local',
        ]);
    }

    $component = Livewire::actingAs($this->user)
        ->test(MediaList::class)
        ->call('downloadCollection', 'attachments', 'attachments')
        ->assertOk()
        ->assertRedirectContains('/media-collection-download/');

    expect($component->effects)->not->toHaveKey('download');
});
