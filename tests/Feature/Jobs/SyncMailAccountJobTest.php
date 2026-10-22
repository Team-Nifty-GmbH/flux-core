<?php

use FluxErp\Contracts\MailSyncDriver;
use FluxErp\Contracts\ReportsSyncProgress;
use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Mail\ImapMailSyncDriver;
use FluxErp\Mail\MailDriverManager;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Traits\IsMonitored;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Webklex\PHPIMAP\Client;

test('sync mail account job is monitored', function (): void {
    expect(is_a(SyncMailAccountJob::class, ShouldBeMonitored::class, true))->toBeTrue()
        ->and(class_uses_recursive(SyncMailAccountJob::class))->toHaveKey(IsMonitored::class);
});

test('sync mail account job name contains the account email', function (): void {
    $mailAccount = MailAccount::factory()->create([
        'email' => 'inbox@example.com',
    ]);

    $job = new SyncMailAccountJob($mailAccount);

    expect($job->getName())->toContain('inbox@example.com');
});

test('imap mail sync driver reports sync progress', function (): void {
    expect(is_a(ImapMailSyncDriver::class, ReportsSyncProgress::class, true))->toBeTrue();
});

test('sync mail account job keeps its unique lock scoped per account and bounded', function (): void {
    expect(is_a(SyncMailAccountJob::class, ShouldBeUnique::class, true))->toBeTrue();

    $first = MailAccount::factory()->create();
    $second = MailAccount::factory()->create();

    expect((new SyncMailAccountJob($first))->uniqueId())
        ->not->toBe((new SyncMailAccountJob($second))->uniqueId())
        ->and((new SyncMailAccountJob($first))->uniqueFor())->toBeGreaterThan(0);
});

test('sync mail account job releases its unique lock after the timeout so a killed run cannot block the account forever', function (): void {
    $account = MailAccount::factory()->create();
    $job = new SyncMailAccountJob($account);
    $lock = new UniqueLock(Cache::store());

    expect($lock->acquire($job))->toBeTrue()
        ->and($lock->acquire($job))->toBeFalse();

    $this->travel($job->uniqueFor() + 1)->seconds();

    expect($lock->acquire($job))->toBeTrue();
});

test('sync mail account job passes a progress callback to the driver', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->state(['is_active' => true]))
        ->create();

    $spy = new class() implements MailSyncDriver, ReportsSyncProgress
    {
        public array $callbacks = [];

        public array $syncedFolders = [];

        public function syncFolders(MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            $this->syncedFolders[] = $folder->getKey();
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }

        public function withProgressCallback(?Closure $callback): static
        {
            $this->callbacks[] = $callback;

            return $this;
        }
    };

    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    (new SyncMailAccountJob($mailAccount))->handle();

    expect($spy->syncedFolders)->toBe([$mailAccount->mailFolders->first()->getKey()])
        ->and(array_filter($spy->callbacks))->toHaveCount(1)
        ->and(end($spy->callbacks))->toBeNull();
});

test('sync mail account job limited to one folder syncs only that folder without listing the folders', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(3)->state(['is_active' => true]))
        ->create();
    $folder = $mailAccount->mailFolders->get(1);

    $spy = new class() implements MailSyncDriver
    {
        public int $folderSyncs = 0;

        public array $syncedFolders = [];

        public function syncFolders(MailAccount $account): array
        {
            $this->folderSyncs++;

            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            $this->syncedFolders[] = $folder->getKey();
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }
    };

    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    (new SyncMailAccountJob($mailAccount, mailFolderId: $folder->getKey()))->handle();

    expect($spy->syncedFolders)->toBe([$folder->getKey()])
        ->and($spy->folderSyncs)->toBe(0);
});

test('sync mail account job hands every folder the account it already holds', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(3)->state(['is_active' => true]))
        ->create();

    $spy = new class() implements MailSyncDriver
    {
        public array $accounts = [];

        public function syncFolders(MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            $this->accounts[] = $folder->mailAccount;
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }
    };

    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    (new SyncMailAccountJob($mailAccount))->handle();

    expect($spy->accounts)->toHaveCount(3)
        ->and($spy->accounts[0])->toBe($mailAccount)
        ->and($spy->accounts[1])->toBe($mailAccount)
        ->and($spy->accounts[2])->toBe($mailAccount);
});

test('sync mail account job keeps syncing the remaining folders when one folder fails', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(3)->state(['is_active' => true]))
        ->create();
    $broken = $mailAccount->mailFolders->first();

    $spy = new class() implements MailSyncDriver
    {
        public ?int $brokenId = null;

        public array $syncedFolders = [];

        public function syncFolders(MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            if ($folder->getKey() === $this->brokenId) {
                throw new RuntimeException('Empty response');
            }

            $this->syncedFolders[] = $folder->getKey();
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }
    };
    $spy->brokenId = $broken->getKey();

    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    expect(fn () => (new SyncMailAccountJob($mailAccount))->handle())
        ->toThrow(RuntimeException::class, 'Empty response');

    expect($spy->syncedFolders)->toEqualCanonicalizing(
        $mailAccount->mailFolders->except($broken->getKey())->modelKeys()
    );
});

test('sync mail account job closes the imap client when it is done', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->state(['is_active' => true]))
        ->create();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('disconnect')->once();

    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($mailAccount, $client);

    app(MailDriverManager::class)->extend('imap', fn () => new class() implements MailSyncDriver
    {
        public function syncFolders(MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(MailFolder $folder): void {}

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }
    });

    (new SyncMailAccountJob($mailAccount))->handle();

    expect((new ReflectionProperty(MailAccount::class, 'imapClient'))->getValue($mailAccount))->toBeNull();
});

test('sync mail account job closes the imap client even when the sync blows up', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->state(['is_active' => true]))
        ->create();

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('disconnect')->once();

    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($mailAccount, $client);

    app(MailDriverManager::class)->extend('imap', fn () => new class() implements MailSyncDriver
    {
        public function syncFolders(MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            throw new RuntimeException('imap is gone');
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }
    });

    expect(fn () => (new SyncMailAccountJob($mailAccount))->handle())
        ->toThrow(RuntimeException::class);

    expect((new ReflectionProperty(MailAccount::class, 'imapClient'))->getValue($mailAccount))->toBeNull();
});

test('sync mail account job stops before its timeout and leaves the rest to the next run', function (): void {
    config(['flux.mail.sync_time_budget' => 3000]);

    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(3)->state(['is_active' => true]))
        ->create();

    $spy = new class() implements MailSyncDriver, ReportsSyncProgress
    {
        public ?Closure $callback = null;

        public array $stored = [];

        public function syncFolders(MailAccount $account): array
        {
            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            foreach ([1, 2, 3] as $uid) {
                $this->stored[] = $folder->getKey() . ':' . $uid;
                $this->travel(20);
                ($this->callback)($uid, 3);
            }
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }

        public function withProgressCallback(?Closure $callback): static
        {
            $this->callback = $callback;

            return $this;
        }

        private function travel(int $minutes): void
        {
            Carbon\Carbon::setTestNow(now()->addMinutes($minutes));
        }
    };

    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    (new SyncMailAccountJob($mailAccount))->handle();

    expect($spy->stored)->toBe([
        $mailAccount->mailFolders->first()->getKey() . ':1',
        $mailAccount->mailFolders->first()->getKey() . ':2',
        $mailAccount->mailFolders->first()->getKey() . ':3',
    ]);
});

function chunkedSyncSpy(): MailSyncDriver
{
    return new class() implements MailSyncDriver, ReportsSyncProgress
    {
        public ?Closure $callback = null;

        public int $folderSyncs = 0;

        public array $stored = [];

        public ?Closure $afterStore = null;

        public function syncFolders(MailAccount $account): array
        {
            $this->folderSyncs++;

            return [];
        }

        public function syncMessages(MailFolder $folder): void
        {
            foreach ([1, 2, 3] as $uid) {
                if (in_array($folder->getKey() . ':' . $uid, $this->stored, true)) {
                    continue;
                }

                $this->stored[] = $folder->getKey() . ':' . $uid;
                Carbon\Carbon::setTestNow(now()->addMinutes(2));

                if ($this->afterStore) {
                    ($this->afterStore)();
                }

                ($this->callback)($uid, 3);
            }
        }

        public function testConnection(MailAccount $account): bool
        {
            return true;
        }

        public function withProgressCallback(?Closure $callback): static
        {
            $this->callback = $callback;

            return $this;
        }
    };
}

test('sync mail account job hands the rest of a long sync to follow-up jobs that resume at the folder it stopped in', function (): void {
    config(['flux.mail.sync_time_budget' => 300]);

    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(3)->state(['is_active' => true]))
        ->create();
    $folders = $mailAccount->mailFolders()->orderBy('id')->pluck('id');

    $spy = chunkedSyncSpy();
    app(MailDriverManager::class)->extend('imap', fn () => $spy);
    $jobs = 0;
    Queue::before(function () use (&$jobs): void {
        $jobs++;
    });

    SyncMailAccountJob::dispatchSync($mailAccount);

    expect($spy->stored)->toBe(
        $folders->flatMap(fn (int $id) => [$id . ':1', $id . ':2', $id . ':3'])->all()
    )
        ->and($jobs)->toBe(4)
        ->and($spy->folderSyncs)->toBe(1);
});

test('sync mail account job stops at once when the queue is paused for maintenance and queues no follow-up', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(3)->state(['is_active' => true]))
        ->create();

    $spy = chunkedSyncSpy();
    $spy->afterStore = fn () => Queue::pauseAll();
    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    $jobs = 0;
    Queue::before(function () use (&$jobs): void {
        $jobs++;
    });

    try {
        SyncMailAccountJob::dispatchSync($mailAccount);
    } finally {
        Queue::resumeAll();
    }

    expect($spy->stored)->toHaveCount(1)
        ->and($jobs)->toBe(1);
});

test('sync mail account job gets through at least one folder per run so a spent budget cannot stall the account', function (): void {
    config(['flux.mail.sync_time_budget' => 0]);

    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory()->count(2)->state(['is_active' => true]))
        ->create();
    $folders = $mailAccount->mailFolders()->orderBy('id')->pluck('id');

    $spy = chunkedSyncSpy();
    app(MailDriverManager::class)->extend('imap', fn () => $spy);

    SyncMailAccountJob::dispatchSync($mailAccount);

    expect($spy->stored)->toBe(
        $folders->flatMap(fn (int $id) => [$id . ':1', $id . ':2', $id . ':3'])->all()
    );
});
