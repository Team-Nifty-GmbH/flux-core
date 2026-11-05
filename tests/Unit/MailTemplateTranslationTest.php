<?php

use Illuminate\Notifications\Messages\MailMessage;

test('german notification mail has no english template strings', function (): void {
    app()->setLocale('de');

    $html = (string) (new MailMessage())
        ->line('Test')
        ->action('Open', 'https://example.com')
        ->render();

    expect($html)
        ->toContain('Mit freundlichen Grüßen,')
        ->not->toContain('Regards,');

    $error = (string) (new MailMessage())->error()->line('Test')->render();

    expect($error)
        ->toContain('Hoppla!')
        ->not->toContain('Whoops!');

    expect(__('All rights reserved.'))->toBe('Alle Rechte vorbehalten.');
});
