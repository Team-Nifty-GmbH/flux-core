<?php

use Carbon\CarbonImmutable;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Jobs\AppendMailToSentFolderJob;
use FluxErp\Mail\ImapMessage;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Support\Facades\Queue;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Connection\Protocols\ProtocolInterface;
use Webklex\PHPIMAP\Connection\Protocols\Response;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Message;

function imapResponse(array $data = []): Response
{
    $response = Mockery::mock(Response::class);
    $response->shouldReceive('validatedData')->andReturn($data);

    return $response;
}

function fakeImapServer(array $folders): ProtocolInterface
{
    $connection = Mockery::mock(ProtocolInterface::class);
    $connection->shouldReceive('folders')->andReturn(imapResponse($folders))->byDefault();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andReturnSelf();
    $client->shouldReceive('disconnect');
    $client->shouldReceive('getDefaultEvents')->andReturn([]);
    $client->shouldReceive('getConnection')->andReturn($connection);

    ImapClient::shouldReceive('make')->andReturn($client);

    return $connection;
}

function sendFluxMail(array $data = []): array
{
    return SendMail::make(array_merge([
        'to' => ['customer@example.com'],
        'subject' => 'Payment reminder',
        'html_body' => '<p>Please pay</p>',
    ], $data))
        ->validate()
        ->execute();
}

beforeEach(function (): void {
    config([
        'mail.default' => 'array',
        'mail.from.address' => 'invoice@example.com',
        'queue.default' => 'sync',
    ]);

    $this->mailAccount = MailAccount::factory()->create([
        'email' => 'invoice@example.com',
        'smtp_email' => 'invoice@example.com',
        'smtp_mailer' => 'array',
    ]);
});

test('a mail sent through a mail account lands in its sent folder', function (): void {
    $connection = fakeImapServer([
        'INBOX' => ['delimiter' => '.', 'flags' => ['\HasNoChildren']],
        'INBOX.Postausgang' => ['delimiter' => '.', 'flags' => ['\HasNoChildren', '\Sent']],
    ]);

    $raw = null;
    $connection->shouldReceive('appendMessage')
        ->once()
        ->withArgs(function (string $folder, string $message, ?array $flags) use (&$raw): bool {
            $raw = $message;

            return $folder === 'INBOX.Postausgang' && $flags === ['\Seen'];
        })
        ->andReturn(imapResponse());

    expect(sendFluxMail(['mail_account_id' => $this->mailAccount->getKey()]))->toHaveKey('success', true);

    $communication = Communication::query()->latest('id')->first();

    expect($communication->mail_account_id)->toBe($this->mailAccount->getKey())
        ->and($communication->message_id)->not->toBeEmpty()
        ->and($raw)->toContain('Message-ID: <' . $communication->message_id . '>');
});

test('a mail from the default mailer lands in the sent folder of the account with that address', function (): void {
    $connection = fakeImapServer([
        'INBOX' => ['delimiter' => '/', 'flags' => []],
        'Gesendete Objekte' => ['delimiter' => '/', 'flags' => []],
    ]);
    $connection->shouldReceive('appendMessage')
        ->once()
        ->withArgs(fn (string $folder) => $folder === 'Gesendete Objekte')
        ->andReturn(imapResponse());

    expect(sendFluxMail())->toHaveKey('success', true)
        ->and(Communication::query()->latest('id')->value('mail_account_id'))->toBe($this->mailAccount->getKey());
});

test('a mailbox without a sent folder only sends', function (): void {
    $connection = fakeImapServer([]);
    $connection->shouldReceive('folders')->once()->andReturn(imapResponse([
        'INBOX' => ['delimiter' => '/', 'flags' => []],
        'Archive' => ['delimiter' => '/', 'flags' => []],
    ]));
    $connection->shouldNotReceive('appendMessage');

    expect(sendFluxMail())->toHaveKey('success', true);
});

test('a failing append does not fail the send', function (): void {
    $connection = fakeImapServer([
        'Sent' => ['delimiter' => '/', 'flags' => []],
    ]);
    $connection->shouldReceive('appendMessage')->once()->andThrow(new RuntimeException('quota exceeded'));

    expect(sendFluxMail(['mail_account_id' => $this->mailAccount->getKey()]))->toHaveKey('success', true);
});

test('a mail from an address without a mail account is not appended', function (): void {
    config(['mail.from.address' => 'noreply@example.com']);

    ImapClient::shouldReceive('make')->never();

    expect(sendFluxMail())->toHaveKey('success', true)
        ->and(Communication::query()->latest('id')->value('mail_account_id'))->toBeNull();
});

test('a nested sent folder is found by its name', function (): void {
    $connection = fakeImapServer([
        'INBOX' => ['delimiter' => '.', 'flags' => []],
        'INBOX.Sent' => ['delimiter' => '.', 'flags' => []],
    ]);
    $connection->shouldReceive('appendMessage')
        ->once()
        ->withArgs(fn (string $folder) => $folder === 'INBOX.Sent')
        ->andReturn(imapResponse());

    expect(sendFluxMail())->toHaveKey('success', true);
});

test('an unreachable imap server does not fail the send', function (): void {
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->once()->andThrow(new ConnectionFailedException('connection refused'));
    ImapClient::shouldReceive('make')->andReturn($client);

    expect(sendFluxMail(['mail_account_id' => $this->mailAccount->getKey()]))->toHaveKey('success', true);
});

test('the copy goes to the mail account the mail was sent with', function (): void {
    Queue::fake();

    $sharedAccount = MailAccount::factory()->create([
        'email' => 'accounting@example.com',
        'smtp_email' => 'invoice@example.com',
        'smtp_mailer' => 'array',
    ]);

    sendFluxMail(['mail_account_id' => $sharedAccount->getKey()]);

    expect(Communication::query()->latest('id')->value('mail_account_id'))->toBe($sharedAccount->getKey());
    Queue::assertPushed(
        AppendMailToSentFolderJob::class,
        fn (AppendMailToSentFolderJob $job) => $job->mailAccount->is($sharedAccount)
    );
});

test('the sync recognizes the appended copy as the sent communication', function (): void {
    Queue::fake();

    sendFluxMail();

    $raw = null;
    Queue::assertPushed(AppendMailToSentFolderJob::class, function (AppendMailToSentFolderJob $job) use (&$raw): bool {
        $raw = $job->message;

        return true;
    });

    $sentFolder = MailFolder::factory()->create(['mail_account_id' => $this->mailAccount->getKey()]);

    $builder = new class($sentFolder) extends ImapMessageBuilder
    {
        public function push(ImapMessage $message): static
        {
            $this->messages->push($message);

            return $this;
        }
    };

    $builder->push(new ImapMessage(
        messageId: Message::fromString($raw)->getMessageId()->toString(),
        uid: 7,
        subject: 'Payment reminder',
        from: 'invoice@example.com',
        to: [],
        cc: [],
        bcc: [],
        textBody: null,
        htmlBody: null,
        date: CarbonImmutable::now(),
        isSeen: true,
        flags: [],
        attachments: [],
    ))
        ->store();

    $communications = Communication::query()->where('mail_account_id', $this->mailAccount->getKey())->get();

    expect($communications)->toHaveCount(1)
        ->and($communications->first()->mail_folder_id)->toBe($sentFolder->getKey())
        ->and($communications->first()->message_uid)->toBe('7');
});

test('a failing queue after delivery does not report the mail as failed', function (): void {
    Queue::shouldReceive('connection')->andThrow(new RuntimeException('redis is down'));

    expect(sendFluxMail())->toHaveKey('success', true);
});
