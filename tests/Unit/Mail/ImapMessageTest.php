<?php

use Carbon\CarbonImmutable;
use FluxErp\Mail\ImapMessage;

test('can be constructed with all properties', function (): void {
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

function imapMessageFrom(string $senderFull, string $subject, string $recipientPersonal): ImapMessage
{
    $address = new Webklex\PHPIMAP\Address((object) [
        'personal' => $recipientPersonal,
        'mailbox' => 'recipient',
        'host' => 'example.com',
        'mail' => 'recipient@example.com',
        'full' => $recipientPersonal . ' <recipient@example.com>',
    ]);

    $message = Mockery::mock(Webklex\PHPIMAP\Message::class);
    $message->shouldReceive('parseBody')->andReturnSelf();
    $message->shouldReceive('getAttachments')->andReturn(new Webklex\PHPIMAP\Support\AttachmentCollection());
    $message->shouldReceive('getMessageId->toString')->andReturn('<broken@example.com>');
    $message->shouldReceive('getUid')->andReturn(7);
    $message->shouldReceive('getSubject->toString')->andReturn($subject);
    $message->shouldReceive('getFrom')->andReturn([(object) ['full' => $senderFull]]);
    $message->shouldReceive('getTo->toArray')->andReturn([$address]);
    $message->shouldReceive('getCc->toArray')->andReturn([]);
    $message->shouldReceive('getBcc->toArray')->andReturn([]);
    $message->shouldReceive('getTextBody')->andReturn("plain \xFF body");
    $message->shouldReceive('getHtmlBody')->andReturn("<p>\xFE</p>");
    $message->shouldReceive('getDate->toDate')->andReturn(new DateTime('2026-01-30 12:00:00'));
    $message->shouldReceive('hasFlag')->andReturn(false);
    $message->shouldReceive('getFlags->toArray')->andReturn([]);

    return ImapMessage::fromImapMessage($message);
}

test('keeps a sender with broken encoding storable as json', function (): void {
    $message = imapMessageFrom("Ren\xE9 M\xFCller <rene@example.com>", 'Betreff', 'Empfänger');

    expect(mb_check_encoding($message->from, 'UTF-8'))->toBeTrue()
        ->and(json_encode($message->from))->not->toBeFalse();
});

test('makes subject and bodies storable as json', function (): void {
    $message = imapMessageFrom('sender@example.com', "Angebot \xC0\xC1", 'Empfänger');

    expect(mb_check_encoding($message->subject, 'UTF-8'))->toBeTrue()
        ->and(mb_check_encoding($message->textBody, 'UTF-8'))->toBeTrue()
        ->and(mb_check_encoding($message->htmlBody, 'UTF-8'))->toBeTrue();
});

test('converts the address objects the recipients are made of', function (): void {
    $message = imapMessageFrom('sender@example.com', 'Betreff', "K\xF6rner");

    expect('Körner')->toBe($message->to[0]->personal)
        ->and(json_encode($message->to))->not->toBeFalse();
});

test('converts a latin encoded sender instead of dropping the characters', function (): void {
    $message = imapMessageFrom("Ren\xE9 M\xFCller <rene@example.com>", 'Betreff', 'Empfänger');

    expect('René Müller <rene@example.com>')->toBe($message->from);
});

test('leaves a clean message untouched', function (): void {
    $message = imapMessageFrom('René Müller <rene@example.com>', 'Angebot für Sie', 'Empfänger');

    expect($message->from)->toBe('René Müller <rene@example.com>')
        ->and($message->subject)->toBe('Angebot für Sie')
        ->and($message->to[0]->personal)->toBe('Empfänger');
});
