<?php

use FluxErp\Livewire\Lead\Comments;
use FluxErp\Models\Comment;
use FluxErp\Models\Lead;
use Livewire\Livewire;

test('loadComments uses the passed model id so a renderless parent can switch records', function (): void {
    $leadA = Lead::factory()->create();
    $leadB = Lead::factory()->create();

    $commentA = Comment::factory()->create([
        'model_type' => morph_alias(Lead::class),
        'model_id' => $leadA->getKey(),
    ]);
    $commentB = Comment::factory()->create([
        'model_type' => morph_alias(Lead::class),
        'model_id' => $leadB->getKey(),
    ]);

    $component = Livewire::test(Comments::class)->instance();

    $resultA = $component->loadComments($leadA->getKey());

    expect($component->modelId)->toBe($leadA->getKey())
        ->and(collect($resultA['data'])->pluck('id'))
        ->toContain($commentA->getKey())
        ->not->toContain($commentB->getKey());

    $resultB = $component->loadComments($leadB->getKey());

    expect($component->modelId)->toBe($leadB->getKey())
        ->and(collect($resultB['data'])->pluck('id'))
        ->toContain($commentB->getKey())
        ->not->toContain($commentA->getKey());
});

test('loadStickyComments uses the passed model id', function (): void {
    $lead = Lead::factory()->create();

    $sticky = Comment::factory()->create([
        'model_type' => morph_alias(Lead::class),
        'model_id' => $lead->getKey(),
        'is_sticky' => true,
    ]);

    $component = Livewire::test(Comments::class)->instance();

    $result = $component->loadStickyComments($lead->getKey());

    expect($component->modelId)->toBe($lead->getKey())
        ->and(collect($result)->pluck('id'))->toContain($sticky->getKey());
});

test('loadComments without a model id returns an empty result', function (): void {
    $component = Livewire::test(Comments::class)->instance();

    expect($component->loadComments())->toBe([]);
});
