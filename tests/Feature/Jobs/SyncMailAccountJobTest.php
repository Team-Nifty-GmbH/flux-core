<?php

use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use FluxErp\Traits\IsMonitored;

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
