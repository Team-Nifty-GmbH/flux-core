<?php

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Sleep;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Connection\Protocols\ImapProtocol;
use Webklex\PHPIMAP\Connection\Protocols\Response;
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

function fakeIdleServer(Closure ...$lines): void
{
    $capabilities = Mockery::mock(Response::class);
    $capabilities->shouldReceive('validatedData')->andReturn(['IMAP4rev1', 'IDLE']);

    $connection = Mockery::mock(ImapProtocol::class);
    $connection->shouldReceive('getCapabilities')->andReturn($capabilities);
    $connection->shouldReceive('idle');
    $connection->shouldReceive('done');
    $connection->shouldReceive('nextLine')->andReturnUsing(...$lines);

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andReturnSelf();
    $client->shouldReceive('openFolder')->with('INBOX', true);
    $client->shouldReceive('getConnection')->andReturn($connection);
    $client->shouldReceive('disconnect');
    ImapClient::shouldReceive('make')->andReturn($client);
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
