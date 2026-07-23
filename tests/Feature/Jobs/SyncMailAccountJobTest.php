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
