<?php

use FluxErp\Mail\ImapHeaderDecoder;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Header;

// Built like the singleton of webklex/laravel-imap, whose provider the test app does not load.
function imapClientManager(): ClientManager
{
    return new ClientManager(config('imap'));
}

function parseImapHeader(string $raw): Header
{
    return new Header($raw, imapClientManager()->getConfig());
}

test('is the header decoder of the imap client', function (): void {
    expect(imapClientManager()->getConfig()->getDecoder('header'))
        ->toBeInstanceOf(ImapHeaderDecoder::class);
});

test('never loads a word of a sender name or subject as a class', function (): void {
    $autoloaded = [];
    $spy = function (string $class) use (&$autoloaded): void {
        $autoloaded[] = $class;
    };
    spl_autoload_register($spy, prepend: true);

    try {
        $header = parseImapHeader("From: Laravel Fluxsender <a@example.com>\r\nSubject: Fluxsubject\r\n");
    } finally {
        spl_autoload_unregister($spy);
    }

    expect($autoloaded)
        ->not->toContain('Laravel')
        ->not->toContain('Fluxsender')
        ->not->toContain('Fluxsubject')
        ->and($header->get('from')->first()->personal)->toBe('Laravel Fluxsender')
        ->and((string) $header->get('subject'))->toBe('Fluxsubject');
});

test('decodes encoded words in addresses and subject', function (string $raw, string $from, string $subject): void {
    $header = parseImapHeader($raw);

    expect($header->get('from')->first()->personal)->toBe($from)
        ->and((string) $header->get('subject'))->toBe($subject);
})->with([
    'utf-8 quoted' => ["From: =?UTF-8?Q?M=C3=BCller?= Hans <a@example.com>\r\nSubject: =?UTF-8?Q?Gr=C3=BC=C3=9Fe?=\r\n", 'Müller Hans', 'Grüße'],
    'utf-8 base64' => ["From: =?UTF-8?B?TcO8bGxlcg==?= <a@example.com>\r\nSubject: =?UTF-8?B?SMOkbGxv?= Welt\r\n", 'Müller', 'Hällo Welt'],
    'latin1' => ["From: =?ISO-8859-1?Q?M=FCller?= <a@example.com>\r\nSubject: =?ISO-8859-1?Q?Gr=FC=DFe?=\r\n", 'Müller', 'Grüße'],
    'adjacent words' => ["From: Hans <a@example.com>\r\nSubject: =?UTF-8?Q?Gr=C3=BC?= =?UTF-8?Q?=C3=9Fe?=\r\n", 'Hans', 'Grüße'],
    'plain ascii' => ["From: Hans Meier <a@example.com>\r\nSubject: Rechnung 4711\r\n", 'Hans Meier', 'Rechnung 4711'],
]);

test('keeps a broken encoded word instead of failing', function (): void {
    $header = parseImapHeader("From: Hans <a@example.com>\r\nSubject: =?nonsense?Q?abc?=\r\n");

    expect((string) $header->get('subject'))->toContain('abc');
});
