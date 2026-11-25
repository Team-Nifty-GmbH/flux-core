<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Mention;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Notifications\MentionNotification;
use Illuminate\Support\Facades\Notification;

test('records record-mentions from comment HTML and subscribes the target', function (): void {
    Notification::fake();
    $actor = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);

    $this->actingAs($actor);

    $comment = Comment::factory()->create([
        'comment' => 'Hänge das an <span class="mention" data-id="ticket:' . $ticket->getKey() . '">#' . $ticket->getKey() . '</span>',
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $ticket->getKey(),
    ]);

    expect(Mention::where('mention_source_id', $comment->getKey())->count())->toBe(1);
});

test('notifies a user mentioned via comment HTML', function (): void {
    Notification::fake();
    $u = User::factory()->create();
    $actor = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);

    $this->actingAs($actor);

    Comment::factory()->create([
        'comment' => 'Hi <span class="mention" data-id="user:' . $u->getKey() . '">@' . $u->name . '</span>',
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $ticket->getKey(),
    ]);

    Notification::assertSentTo($u, MentionNotification::class);
});
