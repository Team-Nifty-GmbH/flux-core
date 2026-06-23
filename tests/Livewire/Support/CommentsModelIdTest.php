<?php

use FluxErp\Livewire\Lead\Comments;
use FluxErp\Models\Comment;
use FluxErp\Models\Lead;
use Livewire\Livewire;

test('loadComments returns the comments for the bound model id', function (): void {
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

    $component->modelId = $leadA->getKey();

    expect(collect($component->loadComments()['data'])->pluck('id'))
        ->toContain($commentA->getKey())
        ->not->toContain($commentB->getKey());

    $component->modelId = $leadB->getKey();

    expect(collect($component->loadComments()['data'])->pluck('id'))
        ->toContain($commentB->getKey())
        ->not->toContain($commentA->getKey());
});

test('loadStickyComments returns nothing when the bound record is not accessible', function (): void {
    Comment::factory()->create([
        'model_type' => morph_alias(Lead::class),
        'model_id' => 999999,
        'is_sticky' => true,
    ]);

    $component = Livewire::test(Comments::class)->instance();
    $component->modelId = 999999;

    expect($component->loadStickyComments())->toBe([]);
});

test('loadStickyComments returns stickies for an accessible record', function (): void {
    $lead = Lead::factory()->create();

    $sticky = Comment::factory()->create([
        'model_type' => morph_alias(Lead::class),
        'model_id' => $lead->getKey(),
        'is_sticky' => true,
    ]);

    $component = Livewire::test(Comments::class)->instance();
    $component->modelId = $lead->getKey();

    expect(collect($component->loadStickyComments())->pluck('id'))->toContain($sticky->getKey());
});

test('loadComments without a model id returns an empty result', function (): void {
    expect(Livewire::test(Comments::class)->instance()->loadComments())->toBe([]);
});
