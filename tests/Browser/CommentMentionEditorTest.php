<?php

use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use function Pest\Browser\visit;

/*
 * Unverified end-to-end coverage for the comment mention editor.
 *
 * Skipped because Playwright is not available in this environment
 * (PlaywrightOutdatedException). Before enabling, verify the selectors against
 * the live editor: the comment input is rendered TWICE — once inside
 * `<template x-ref="textarea">` (inert clone) and once as the real form — so
 * the `.comment-input [contenteditable]` selector may need scoping to the
 * visible instance. There is no "open comment form" trigger; the form is always
 * rendered when the user may create comments.
 */
it('mentions a user in a comment and persists the @user token', function (): void {
    $user = User::factory()->create(['firstname' => 'Findus']);
    $actor = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => morph_alias(User::class),
        'authenticatable_id' => $actor->id,
    ]);

    $page = visit('/tickets/' . $ticket->getKey())->actingAs($actor);

    $page->type('.comment-input [contenteditable="true"]', '@Findus')
        ->wait(500)
        ->click('.suggestion-item:first-child')
        ->click('[data-test="comment-submit"]')
        ->assertSee('Findus');

    expect(Comment::latest()->first()->comment)
        ->toContain('data-id="user:' . $user->getKey() . '"');
})->skip('Playwright unavailable; verify selectors against the live comment editor before enabling.');
