<?php

use FluxErp\Mail\MagicLoginLink;

test('magic login link has correct subject', function (): void {
    $mail = MagicLoginLink::make('https://example.com/login/abc123');

    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Login Link');
});

test('magic login link content has url', function (): void {
    $mail = MagicLoginLink::make('https://example.com/login/abc123');

    $content = $mail->content();

    expect($content->with['url'])->toBe('https://example.com/login/abc123');
});

test('magic login link uses markdown template', function (): void {
    $mail = MagicLoginLink::make('https://example.com/login/abc123');

    $content = $mail->content();

    expect($content->markdown)->toBe('flux::emails.magic-login-link');
});
