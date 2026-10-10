<?php

use FluxErp\Support\Mail\MailServerDiscovery;
use Illuminate\Support\Facades\Http;

function fakeMailDns(array $records = [], int $deadlineSeconds = 8): void
{
    app()->bind(
        MailServerDiscovery::class,
        fn () => new class($records, $deadlineSeconds) extends MailServerDiscovery
        {
            public function __construct(private readonly array $records, int $deadlineSeconds)
            {
                $this->deadlineSeconds = $deadlineSeconds;
            }

            protected function dnsRecords(string $host, int $type): array
            {
                // Every host resolves to a public address unless a test says otherwise.
                return $this->records[$type][$host] ?? ($type === DNS_A ? [['ip' => '93.184.216.34']] : []);
            }
        }
    );
}

function autoconfigXml(
    string $imapHost = 'imap.example.com',
    string $imapSocket = 'SSL',
    string $smtpHost = 'smtp.example.com',
    string $smtpSocket = 'STARTTLS',
    string $smtpUser = '%EMAILADDRESS%'
): string {
    return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <clientConfig version="1.1">
          <emailProvider id="example.com">
            <domain>example.com</domain>
            <incomingServer type="pop3">
              <hostname>pop.example.com</hostname>
              <port>995</port>
              <socketType>SSL</socketType>
            </incomingServer>
            <incomingServer type="imap">
              <hostname>{$imapHost}</hostname>
              <port>993</port>
              <socketType>{$imapSocket}</socketType>
              <username>%EMAILADDRESS%</username>
            </incomingServer>
            <outgoingServer type="smtp">
              <hostname>{$smtpHost}</hostname>
              <port>587</port>
              <socketType>{$smtpSocket}</socketType>
              <username>{$smtpUser}</username>
            </outgoingServer>
          </emailProvider>
        </clientConfig>
        XML;
}

beforeEach(function (): void {
    fakeMailDns();
});

test('provider autoconfig is used', function (): void {
    Http::fake([
        'autoconfig.example.com/*' => Http::response(autoconfigXml()),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))->toBe([
        'host' => 'imap.example.com',
        'port' => 993,
        'encryption' => 'ssl',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'smtp_user' => 'john@example.com',
        'source' => 'autoconfig.example.com',
    ]);
});

test('well-known autoconfig is used', function (): void {
    Http::fake([
        'example.com/.well-known/autoconfig/*' => Http::response(autoconfigXml('imap.wk.com')),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->host->toBe('imap.wk.com')
        ->source->toBe('example.com');
});

test('ispdb is used', function (): void {
    Http::fake([
        'autoconfig.thunderbird.net/v1.1/example.com' => Http::response(autoconfigXml('imap.ispdb.com')),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->host->toBe('imap.ispdb.com')
        ->source->toBe('autoconfig.thunderbird.net');
});

test('ispdb is queried for the base domain of the mx', function (): void {
    fakeMailDns([
        DNS_MX => [
            'example.com' => [
                ['target' => 'alt1.aspmx.l.google.com', 'pri' => 5],
                ['target' => 'aspmx.l.google.com', 'pri' => 1],
            ],
        ],
    ]);
    Http::fake([
        'autoconfig.thunderbird.net/v1.1/google.com' => Http::response(
            autoconfigXml('imap.gmail.com', 'SSL', 'smtp.gmail.com', 'SSL')
        ),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->host->toBe('imap.gmail.com')
        ->smtp_host->toBe('smtp.gmail.com')
        ->smtp_encryption->toBe('ssl')
        ->source->toBe('autoconfig.thunderbird.net');
});

test('dns srv records are used', function (): void {
    fakeMailDns([
        DNS_SRV => [
            '_imaps._tcp.example.com' => [['target' => 'mail.example.com', 'port' => 993, 'pri' => 0]],
            '_submission._tcp.example.com' => [
                ['target' => 'backup.example.com', 'port' => 587, 'pri' => 10],
                ['target' => 'smtp.example.com', 'port' => 587, 'pri' => 0],
            ],
            '_submissions._tcp.example.com' => [['target' => '.', 'port' => 0, 'pri' => 0]],
        ],
    ]);
    Http::fake(['*' => Http::response('', 404)]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))->toBe([
        'host' => 'mail.example.com',
        'port' => 993,
        'encryption' => 'ssl',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'source' => 'DNS SRV',
    ]);
});

test('an earlier source wins', function (): void {
    Http::fake([
        'autoconfig.example.com/*' => Http::response(autoconfigXml('imap.first.com')),
        'example.com/.well-known/*' => Http::response(autoconfigXml('imap.second.com')),
        'autoconfig.thunderbird.net/*' => Http::response(autoconfigXml('imap.third.com')),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->host->toBe('imap.first.com');
});

test('username placeholders are resolved and plain maps to no encryption', function (): void {
    Http::fake([
        'autoconfig.example.com/*' => Http::response(
            autoconfigXml(smtpSocket: 'plain', smtpUser: '%EMAILLOCALPART%+x@%EMAILDOMAIN%')
        ),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->smtp_user->toBe('john+x@example.com')
        ->smtp_encryption->toBeNull();
});

test('non 200 and invalid xml fall through to the next source', function (): void {
    Http::fake([
        'autoconfig.example.com/*' => Http::response(autoconfigXml('imap.error.com'), 500),
        'example.com/.well-known/*' => Http::response('<html>not xml'),
        'autoconfig.thunderbird.net/*' => Http::response(autoconfigXml('imap.ispdb.com')),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->host->toBe('imap.ispdb.com');
});

test('returns null when nothing is found', function (): void {
    Http::fake(['*' => Http::response('', 404)]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))->toBeNull();
});

test('a host resolving to a private address is never queried', function (): void {
    fakeMailDns([DNS_A => ['autoconfig.example.com' => [['ip' => '10.0.0.5']]]]);
    Http::fake([
        'autoconfig.example.com/*' => Http::response(autoconfigXml()),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))->toBeNull();
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'autoconfig.example.com'));
});

test('an oversized response falls through to the next source', function (): void {
    Http::fake([
        'autoconfig.example.com/*' => Http::response(autoconfigXml() . '<!--' . str_repeat('x', 1024 * 1024) . '-->'),
        '*' => Http::response('', 404),
    ]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))->toBeNull();
});

test('http sources are skipped once the deadline has passed', function (): void {
    fakeMailDns([
        DNS_SRV => ['_imaps._tcp.example.com' => [['target' => 'mail.example.com', 'port' => 993, 'pri' => 0]]],
    ], deadlineSeconds: 0);
    Http::fake(['*' => Http::response(autoconfigXml())]);

    expect(app(MailServerDiscovery::class)->discover('john@example.com'))
        ->toMatchArray(['host' => 'mail.example.com', 'source' => 'DNS SRV']);
    Http::assertNothingSent();
});
