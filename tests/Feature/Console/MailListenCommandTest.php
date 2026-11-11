<?php

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Bus\UniqueLock;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Sleep;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Connection\Protocols\ImapProtocol;
use Webklex\PHPIMAP\Connection\Protocols\Response;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

function listeningMailAccount(): array
{
    $mailAccount = MailAccount::factory()->create(['host' => 'imap.example.com']);
    $inbox = MailFolder::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'slug' => 'INBOX',
        'is_active' => true,
    ]);

    return [$mailAccount, $inbox];
}

function fakeIdleConnection(Closure ...$lines): ImapProtocol
{
    $capabilities = Mockery::mock(Response::class);
    $capabilities->shouldReceive('validatedData')->andReturn(['IMAP4rev1', 'IDLE']);

    $connection = Mockery::mock(ImapProtocol::class);
    $connection->shouldReceive('getCapabilities')->andReturn($capabilities);
    $connection->shouldReceive('meta')->andReturn(['eof' => false])->byDefault();
    $connection->shouldReceive('idle')->byDefault();
    $connection->shouldReceive('done')->byDefault();
    $connection->shouldReceive('nextLine')->andReturnUsing(...$lines);

    return $connection;
}

function fakeImapClient(ImapProtocol $connection): Client
{
    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andReturnSelf();
    $client->shouldReceive('openFolder')->with('INBOX', true);
    $client->shouldReceive('getConnection')->andReturn($connection);
    $client->shouldReceive('disconnect');

    return $client;
}

function fakeIdleServer(Closure ...$lines): void
{
    ImapClient::shouldReceive('make')->andReturn(fakeImapClient(fakeIdleConnection(...$lines)));
}

function restartListener(): void
{
    Cache::forever('illuminate:queue:restart', now()->timestamp);
}

test('the listener starts one process per imap account with an active inbox', function (): void {
    Process::fake();
    Sleep::fake();
    Sleep::whenFakingSleep(fn () => restartListener());

    $listening = MailAccount::factory()
        ->has(MailFolder::factory()->state(['slug' => 'INBOX', 'is_active' => true]))
        ->create(['host' => 'imap.example.com']);
    MailAccount::factory()
        ->has(MailFolder::factory()->state(['slug' => 'INBOX', 'is_active' => true]))
        ->create(['host' => 'imap.example.com', 'failed_logins' => MailAccount::MAX_FAILED_LOGINS]);
    MailAccount::factory()
        ->has(MailFolder::factory()->state(['slug' => 'INBOX', 'is_active' => false]))
        ->create(['host' => 'imap.example.com']);
    MailAccount::factory()
        ->has(MailFolder::factory()->state(['slug' => 'INBOX', 'is_active' => true]))
        ->create(['host' => null]);

    $this->artisan('flux:mail-listen')->assertSuccessful();

    Process::assertRanTimes(fn (PendingProcess $process): bool => true, 1);
    Process::assertRan(
        fn (PendingProcess $process): bool => in_array('--account=' . $listening->getKey(), $process->command)
    );
});

test('the listener restarts the process of an account whose settings changed', function (): void {
    Process::fake(['*' => Process::describe()->runsFor(iterations: 100)]);
    Sleep::fake();

    [$mailAccount] = listeningMailAccount();

    $sleeps = 0;
    Sleep::whenFakingSleep(function () use (&$sleeps, $mailAccount): void {
        if (++$sleeps === 1) {
            $this->travel(1)->minute();
            $mailAccount->update(['host' => 'imap.changed.example.com']);

            return;
        }

        restartListener();
    });

    $this->artisan('flux:mail-listen')->assertSuccessful();

    Process::assertRanTimes(fn (PendingProcess $process): bool => true, 2);
});

test('a new message in the inbox syncs only the inbox of that account', function (): void {
    Queue::fake();
    [$mailAccount, $inbox] = listeningMailAccount();

    fakeIdleServer(function (): string {
        restartListener();

        return "* 12 exists\r\n";
    });

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])->assertSuccessful();

    Queue::assertPushed(
        SyncMailAccountJob::class,
        fn (SyncMailAccountJob $job): bool => $job->mailFolderId === $inbox->getKey()
    );
});

test('a new message that arrives while the account syncs is synced once that sync is done', function (): void {
    Queue::fake();
    [$mailAccount, $inbox] = listeningMailAccount();

    $lock = new UniqueLock(app(Repository::class));
    $runningSync = new SyncMailAccountJob($mailAccount);
    $lock->acquire($runningSync);

    fakeIdleServer(
        fn (): string => "* 12 EXISTS\r\n",
        function () use ($lock, $runningSync): string {
            Queue::assertNothingPushed();
            $lock->release($runningSync);

            throw new RuntimeException('empty response');
        },
        function (): string {
            restartListener();

            throw new RuntimeException('empty response');
        },
    );

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])->assertSuccessful();

    Queue::assertPushed(
        SyncMailAccountJob::class,
        fn (SyncMailAccountJob $job): bool => $job->mailFolderId === $inbox->getKey()
    );
});

test('a listener ends with invalid when the server does not support idle', function (): void {
    [$mailAccount] = listeningMailAccount();

    $capabilities = Mockery::mock(Response::class);
    $capabilities->shouldReceive('validatedData')->andReturn(['IMAP4rev1']);

    $connection = Mockery::mock(ImapProtocol::class);
    $connection->shouldReceive('getCapabilities')->andReturn($capabilities);
    $connection->shouldNotReceive('idle');

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andReturnSelf();
    $client->shouldReceive('getConnection')->andReturn($connection);
    $client->shouldReceive('disconnect');
    ImapClient::shouldReceive('make')->andReturn($client);

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])
        ->assertExitCode(Command::INVALID);
});

test('the listener does not restart an account whose server does not support idle', function (): void {
    Process::fake(['*' => Process::result(exitCode: Command::INVALID)]);
    Sleep::fake();
    listeningMailAccount();

    $sleeps = 0;
    Sleep::whenFakingSleep(function () use (&$sleeps): void {
        if (++$sleeps === 3) {
            restartListener();
        }
    });

    $this->artisan('flux:mail-listen')->assertSuccessful();

    Process::assertRanTimes(fn (PendingProcess $process): bool => true, 1);
});

test('the listener renews idle every few minutes so a dead connection shows up early', function (): void {
    [$mailAccount] = listeningMailAccount();

    $connection = fakeIdleConnection(
        function (): string {
            $this->travel(6)->minutes();

            throw new RuntimeException('empty response');
        },
        function (): string {
            restartListener();

            throw new RuntimeException('empty response');
        },
    );
    $connection->shouldReceive('idle')->twice();
    ImapClient::shouldReceive('make')->andReturn(fakeImapClient($connection));

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])->assertSuccessful();
});

test('a dead connection is reconnected silently and the inbox synced', function (string $failure): void {
    Exceptions::fake();
    Queue::fake();
    Sleep::fake();
    [$mailAccount, $inbox] = listeningMailAccount();

    $deadConnection = fakeIdleConnection(function (): string {
        $this->travel(6)->minutes();

        throw new RuntimeException('empty response');
    });
    match ($failure) {
        'eof' => $deadConnection->shouldReceive('meta')->andReturn(['eof' => true]),
        'timeout' => $deadConnection->shouldReceive('done')->andThrow(new RuntimeException('empty response')),
        'broken pipe' => $deadConnection->shouldReceive('done')->andThrow(
            new ErrorException('fwrite(): SSL operation failed with code 5. OpenSSL Error messages: Broken pipe')
        ),
    };

    $newConnection = fakeIdleConnection(function (): string {
        restartListener();

        throw new RuntimeException('empty response');
    });

    ImapClient::shouldReceive('make')
        ->twice()
        ->andReturn(fakeImapClient($deadConnection), fakeImapClient($newConnection));

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])->assertSuccessful();

    Exceptions::assertNothingReported();
    Queue::assertPushed(
        SyncMailAccountJob::class,
        fn (SyncMailAccountJob $job): bool => $job->mailFolderId === $inbox->getKey()
    );
})->with([
    'the server closed the connection' => 'eof',
    'the server stopped answering' => 'timeout',
    'writing to the connection fails' => 'broken pipe',
]);

test('a listener reports once when reconnecting keeps failing for half an hour', function (): void {
    Exceptions::fake();
    Sleep::fake();
    [$mailAccount] = listeningMailAccount();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andThrow(new ConnectionFailedException('connection setup failed'));
    $client->shouldReceive('disconnect');
    ImapClient::shouldReceive('make')->andReturn($client);

    $sleeps = 0;
    Sleep::whenFakingSleep(function () use (&$sleeps): void {
        $this->travel(10)->minutes();

        if (++$sleeps === 6) {
            restartListener();
        }
    });

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])->assertSuccessful();

    Exceptions::assertReportedCount(1);
    Exceptions::assertReported(ConnectionFailedException::class);
});
