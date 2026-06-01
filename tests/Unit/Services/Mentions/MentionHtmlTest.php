<?php

use FluxErp\Services\Mentions\MentionHtml;

it('converts a user mention span to a token', function (): void {
    $html = 'Hi <span class="mention" data-type="mention" data-id="user:42">@Max</span>!';

    expect(MentionHtml::toTokens($html))->toBe('Hi @user:42!');
});

it('converts a record mention span to a token', function (): void {
    $html = 'See <span data-id="ticket:7">#7</span>';

    expect(MentionHtml::toTokens($html))->toBe('See #ticket:7');
});

it('converts multiple spans in one body', function (): void {
    $html = '<span data-id="user:1">@a</span> and <span data-id="order:9">O-9</span>';

    expect(MentionHtml::toTokens($html))->toBe('@user:1 and #order:9');
});

it('leaves existing plain tokens and unrelated markup untouched', function (): void {
    $html = '<p>plain @user:5 and <strong>bold</strong></p>';

    expect(MentionHtml::toTokens($html))->toBe('<p>plain @user:5 and <strong>bold</strong></p>');
});

it('is idempotent', function (): void {
    $html = '<span data-id="user:42">@Max</span>';
    $once = MentionHtml::toTokens($html);

    expect($once)->toBe('@user:42');
    expect(MentionHtml::toTokens($once))->toBe($once);
});

it('leaves spans whose data-id is not a key:id mention untouched', function (): void {
    $html = '<span data-id="foo">x</span> and <span data-id="user:abc">y</span>';

    expect(MentionHtml::toTokens($html))->toBe($html);
});
