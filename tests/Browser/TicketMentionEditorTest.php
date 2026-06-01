<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use function Pest\Browser\visit;

/*
 * Unverified end-to-end coverage for cross-referencing a Ticket from a comment.
 *
 * Skipped because Playwright is not available in this environment
 * (PlaywrightOutdatedException). See CommentMentionEditorTest for the selector
 * caveats (duplicated comment input, no open-form trigger).
 */
it('mentions a ticket from inside a comment and persists the @ticket token', function (): void {
    $actor = User::factory()->create();
    $referenced = Ticket::factory()->create([
        'title' => 'Reference Me',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);
    $home = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);

    $page = visit('/tickets/' . $home->getKey())->actingAs($actor);

    $page->type('.comment-input [contenteditable="true"]', '#Reference')
        ->wait(500)
        ->click('.suggestion-item:first-child')
        ->click('[data-test="comment-submit"]')
        ->assertSee('Reference Me');

    expect(Comment::latest()->first()->comment)
        ->toContain('data-id="ticket:' . $referenced->getKey() . '"');
})->skip('Playwright unavailable; verify selectors against the live comment editor before enabling.');
