<?php

use Carbon\CarbonImmutable;
use FluxErp\Mail\ImapMessage;

it('can be constructed with all properties', function (): void {
    $message = new ImapMessage(
        messageId: '<test@example.com>',
        uid: 42,
        subject: 'Test Subject',
        from: 'sender@example.com',
        to: [['mail' => 'recipient@example.com', 'personal' => 'Recipient']],
        cc: [],
        bcc: [],
        textBody: 'Hello World',
        htmlBody: '<p>Hello World</p>',
        date: CarbonImmutable::parse('2026-01-30 12:00:00'),
        isSeen: false,
        flags: ['recent'],
        attachments: [],
    );

    expect($message->messageId)->toBe('<test@example.com>')
        ->and($message->uid)->toBe(42)
        ->and($message->subject)->toBe('Test Subject')
        ->and($message->from)->toBe('sender@example.com')
        ->and($message->isSeen)->toBeFalse()
        ->and($message->attachments)->toBeEmpty();
});
