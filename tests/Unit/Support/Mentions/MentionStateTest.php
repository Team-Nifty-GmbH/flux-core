<?php

use FluxErp\Support\Mentions\MentionState;

test('builds a Tailwind theme color variable from a known token', function (): void {
    $state = new MentionState('In Progress', 'violet');

    expect($state->label)->toBe('In Progress');
    expect($state->color)->toBe('violet');
    expect($state->cssColor())->toBe('var(--color-violet-500)');
});

test('falls back to currentColor for an unsafe color token', function (): void {
    expect((new MentionState('X', 'red; background:url(x)'))->cssColor())->toBe('currentColor');
});

test('falls back to currentColor for a mixed-case color token', function (): void {
    expect((new MentionState('X', 'Blue'))->cssColor())->toBe('currentColor');
});

test('renders escaped pill attributes including the state custom property', function (): void {
    $attrs = (new MentionState('In Progress', 'violet'))->toPillAttributes();

    expect($attrs)->toContain('data-mention-state="In Progress"');
    expect($attrs)->toContain('title="In Progress"');
    expect($attrs)->toContain('style="--mention-state-color: var(--color-violet-500)"');
});

test('escapes the label in attributes', function (): void {
    $attrs = (new MentionState('A & "B"', 'green'))->toPillAttributes();

    expect($attrs)->toContain('data-mention-state="A &amp; &quot;B&quot;"');
    expect($attrs)->not->toContain('"B"');
});
