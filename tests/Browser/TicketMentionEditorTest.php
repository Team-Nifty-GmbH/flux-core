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
test('mentions a ticket from inside a comment and persists the @ticket token', function (): void {
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

/*
 * Documents the #-scope chip flow: typing the bare trigger surfaces a scope
 * chip per mentionable type; choosing one rewrites the query to "#ticket:" and
 * keeps the suggestion open so the rest of the term is searched within that
 * type only.
 */
test('scopes the # search to tickets via a scope chip', function (): void {
    $actor = User::factory()->create();
    $referenced = Ticket::factory()->create([
        'title' => 'Scope Target',
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);
    $home = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);

    $page = visit('/tickets/' . $home->getKey())->actingAs($actor);

    $page->type('.comment-input [contenteditable="true"]', '#')
        ->wait(500)
        ->click('.suggestion-scope:first-child')
        ->type('.comment-input [contenteditable="true"]', 'Scope')
        ->wait(500)
        ->click('.suggestion-item:first-child')
        ->click('[data-test="comment-submit"]')
        ->assertSee('Scope Target');

    expect(Comment::latest()->first()->comment)
        ->toContain('data-id="ticket:' . $referenced->getKey() . '"');
})->skip('Playwright unavailable; verify selectors against the live comment editor before enabling.');
