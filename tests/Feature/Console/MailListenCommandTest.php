<?php

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Sleep;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Connection\Protocols\ImapProtocol;

test('the listener starts one process per imap account with an active inbox', function (): void {
    Process::fake();
    Sleep::fake();
    Sleep::whenFakingSleep(fn () => Cache::forever('illuminate:queue:restart', now()->timestamp));

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

test('a new message in the inbox syncs only the inbox of that account', function (): void {
    Queue::fake();

    $mailAccount = MailAccount::factory()->create(['host' => 'imap.example.com']);
    $inbox = MailFolder::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'slug' => 'INBOX',
        'is_active' => true,
    ]);

    $connection = Mockery::mock(ImapProtocol::class);
    $connection->shouldReceive('idle');
    $connection->shouldReceive('done');
    $connection->shouldReceive('nextLine')
        ->andReturnUsing(function (): string {
            Cache::forever('illuminate:queue:restart', now()->timestamp);

            return "* 12 EXISTS\r\n";
        });

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('connect')->andReturnSelf();
    $client->shouldReceive('openFolder')->with('INBOX', true);
    $client->shouldReceive('getConnection')->andReturn($connection);
    $client->shouldReceive('disconnect');
    ImapClient::shouldReceive('make')->andReturn($client);

    $this->artisan('flux:mail-listen', ['--account' => $mailAccount->getKey()])->assertSuccessful();

    Queue::assertPushed(
        SyncMailAccountJob::class,
        fn (SyncMailAccountJob $job): bool => $job->mailFolderId === $inbox->getKey()
    );
});
