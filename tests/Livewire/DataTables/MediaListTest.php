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

test('downloadMedia triggers a client-side redirect and never dehydrates the file content', function (): void {
    Storage::fake('local');

    $task = Task::factory()->create([
        'responsible_user_id' => $this->user->getKey(),
    ]);

    /** @var Media $media */
    $media = Media::factory()->create([
        'model_type' => $task->getMorphClass(),
        'model_id' => $task->getKey(),
        'disk' => 'local',
    ]);
    Storage::disk('local')->put($media->getPathRelativeToRoot(), 'mock-pdf-bytes');

    $component = Livewire::actingAs($this->user)
        ->test(MediaList::class)
        ->call('downloadMedia', $media->getKey())
        ->assertReturned(true);

    // SupportFileDownloads dehydrates BinaryFileResponse return values into
    // the response payload as base64. Asserting the absence of the 'download'
    // effect and the presence of an 'xjs' window.location.href is the
    // observable proof that a 70 MB attachment will not exhaust 128 MB of
    // request memory.
    expect($component->effects)->not->toHaveKey('download');
    expect($component->effects['xjs'] ?? [])
        ->toHaveCount(1)
        ->and($component->effects['xjs'][0]['expression'])
        ->toStartWith('window.location.href = ');
});
