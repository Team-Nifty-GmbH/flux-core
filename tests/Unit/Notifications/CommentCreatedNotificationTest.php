<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Language;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Notifications\Comment\CommentCreatedNotification;

beforeEach(function (): void {
    $language = Language::factory()->create();

    $this->user = User::factory()->create(['language_id' => $language->id]);

    $this->task = Task::factory()->create([
        'name' => 'Test Task',
        'responsible_user_id' => $this->user->id,
    ]);

    $this->ticket = Ticket::factory()->create([
        'title' => 'Test Ticket',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $this->user->id,
    ]);

    $this->taskComment = Comment::factory()->create([
        'model_type' => morph_alias(Task::class),
        'model_id' => $this->task->id,
        'comment' => 'Test comment on task',
    ]);

    $this->ticketComment = Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $this->ticket->id,
        'comment' => 'Test comment on ticket',
    ]);
});

test('getRoute returns parent model detail route for task comment', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $route = $notification->getRoute();

    expect($route)->toBe($this->task->detailRoute());
});

test('getRoute returns parent model detail route for ticket comment', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = $this->ticketComment;

    $route = $notification->getRoute();

    expect($route)->toBe($this->ticket->detailRoute());
});

test('getRoute returns null when comment has no parent model', function (): void {
    $comment = Comment::factory()->create([
        'model_type' => morph_alias(Task::class),
        'model_id' => 99999,
        'comment' => 'Orphan comment',
    ]);

    $notification = new CommentCreatedNotification();
    $notification->model = $comment;

    $route = $notification->getRoute();

    expect($route)->toBeNull();
});

test('getRoute returns null when notification model is null', function (): void {
    $notification = new CommentCreatedNotification();
    $notification->model = null;

    $route = $notification->getRoute();

    expect($route)->toBeNull();
});

test('toArray includes url in accept action', function (): void {
    $this->actingAs($this->user);

    $notification = new CommentCreatedNotification();
    $notification->model = $this->taskComment;

    $array = $notification->toArray($this->user);

    expect($array)
        ->toHaveKey('accept')
        ->and($array['accept'])
        ->toHaveKey('url')
        ->and($array['accept']['url'])
        ->toBe($this->task->detailRoute());
});
