<?php

use FluxErp\Livewire\Ticket\Comments;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(Comments::class)
        ->assertOk();
});

test('renders with ticket model id', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->assertOk();
});

test('can save a comment on a ticket', function (): void {
    $commentText = 'This is a test comment';

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('saveComment', ['comment' => $commentText])
        ->assertReturned(function (?array $result) use ($commentText): true {
            expect($result)->not->toBeNull()
                ->and($result['comment'])->toBe($commentText);

            return true;
        });

    $this->assertDatabaseHas('comments', [
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'comment' => $commentText,
    ]);
});

test('save comment returns null on validation failure', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('saveComment', ['comment' => null])
        ->assertReturned(null);
});

test('can load comments for a ticket', function (): void {
    Comment::factory()->count(3)->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('loadComments')
        ->assertReturned(function (array $comments): true {
            expect(data_get($comments, 'data'))->toHaveCount(3);

            return true;
        });
});

test('can load sticky comments for a ticket', function (): void {
    Comment::factory()->count(2)->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
    ]);

    Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'is_sticky' => true,
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('loadStickyComments')
        ->assertReturned(function (array $stickyComments): true {
            expect($stickyComments)->toHaveCount(1);

            return true;
        });
});

test('can toggle sticky on a comment', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
        'is_sticky' => false,
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('toggleSticky', $comment->getKey());

    $this->assertDatabaseHas('comments', [
        'id' => $comment->getKey(),
        'is_sticky' => true,
    ]);
});

test('can delete a comment', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->getKey(),
    ]);

    Livewire::withoutLazyLoading()
        ->test(Comments::class, ['modelId' => $this->ticket->getKey()])
        ->call('delete', $comment->getKey())
        ->assertReturned(true);

    $this->assertSoftDeleted('comments', [
        'id' => $comment->getKey(),
    ]);
});

test('load comments returns empty for no model id', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class)
        ->call('loadComments')
        ->assertReturned([]);
});
