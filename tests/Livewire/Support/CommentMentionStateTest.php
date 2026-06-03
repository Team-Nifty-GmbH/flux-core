<?php

use FluxErp\Livewire\Ticket\Comments;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\WaitingForSupport;
use Illuminate\Support\Arr;
use Livewire\Livewire;

it('returns the mentioned ticket current state when loading comments', function (): void {
    $host = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(get_class($this->user)),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    $referenced = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(get_class($this->user)),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    $key = $referenced::mentionTypeKey();

    Comment::query()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $host->getKey(),
        'comment' => '<p>See <a data-id="' . $key . ':' . $referenced->getKey()
            . '" data-mention-type="Ticket" href="/tickets/' . $referenced->getKey() . '">#'
            . $referenced->getKey() . '</a></p>',
        'is_internal' => false,
    ]);

    $referenced->update(['state' => Done::class]);

    $component = Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $host->getKey()]);

    $comments = $component->instance()->loadComments();
    $body = Arr::get($comments, 'data.0.comment', '');

    $mentionState = $referenced->fresh()->getMentionState();

    expect($body)->toContain('data-mention-state="' . $mentionState->label . '"')
        ->and($body)->toContain('var(--color-' . $mentionState->color . '-500)');
});

it('returns the mentioned ticket current state for child/reply comments when loading comments', function (): void {
    $host = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(get_class($this->user)),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    $referenced = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(get_class($this->user)),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    $key = $referenced::mentionTypeKey();

    $parent = Comment::query()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $host->getKey(),
        'comment' => '<p>Top-level comment</p>',
        'is_internal' => false,
    ]);

    Comment::query()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $host->getKey(),
        'parent_id' => $parent->getKey(),
        'comment' => '<p>Reply with <a data-id="' . $key . ':' . $referenced->getKey()
            . '" data-mention-type="Ticket" href="/tickets/' . $referenced->getKey() . '">#'
            . $referenced->getKey() . '</a></p>',
        'is_internal' => false,
    ]);

    $referenced->update(['state' => Done::class]);

    $component = Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $host->getKey()]);

    $comments = $component->instance()->loadComments();
    $body = Arr::get($comments, 'data.0.children.0.comment', '');

    $mentionState = $referenced->fresh()->getMentionState();

    expect($body)->toContain('data-mention-state="' . $mentionState->label . '"')
        ->and($body)->toContain('var(--color-' . $mentionState->color . '-500)');
});
