<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Mention;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Services\Mentions\MentionHtml;
use FluxErp\Services\Mentions\MentionRenderer;
use Illuminate\Support\Facades\Notification;

it('deletes mention rows when the source comment is deleted', function (): void {
    Notification::fake();
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    $comment = Comment::factory()->create([
        'comment' => "@user:{$user->getKey()}",
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $ticket->getKey(),
    ]);

    expect(Mention::count())->toBe(1);

    $comment->delete();

    expect(Mention::count())->toBe(0);
});

it('renders fallback pill for deleted record target', function (): void {
    Notification::fake();
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $user->id,
    ]);

    $comment = Comment::factory()->create([
        'comment' => "#ticket:{$ticket->getKey()}",
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $ticket->getKey(),
    ]);

    $ticket->delete();

    $html = app(MentionRenderer::class)
        ->tokensToHtml(MentionHtml::toTokens($comment->fresh()->comment));

    expect($html)->toContain(__('@deleted entry'));
});
