<?php

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use FluxErp\Models\Task;

beforeEach(function (): void {
    $this->task = Task::factory()->create();
});

test('create comment', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'This is a test comment',
    ])->validate()->execute();

    expect($comment)
        ->comment->toContain('test comment')
        ->is_internal->toBeTruthy();
});

test('create comment defaults to internal', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Internal note',
    ])->validate()->execute();

    expect($comment->is_internal)->toBeTruthy();
});

test('create comment requires model_type model_id and comment', function (): void {
    CreateComment::assertValidationErrors([], ['model_type', 'model_id', 'comment']);
});

test('update comment', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'Original',
        'is_internal' => true,
        'is_sticky' => false,
    ])->validate()->execute();

    $updated = UpdateComment::make([
        'id' => $comment->getKey(),
        'comment' => 'Updated comment',
        'is_internal' => true,
        'is_sticky' => false,
    ])->validate()->execute();

    expect($updated->comment)->not->toBeNull();
});

test('delete comment', function (): void {
    $comment = CreateComment::make([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->getKey(),
        'comment' => 'To delete',
        'is_internal' => true,
        'is_sticky' => false,
    ])->validate()->execute();

    expect(DeleteComment::make(['id' => $comment->getKey()])
        ->validate()->execute())->toBeTrue();
});
