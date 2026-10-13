<?php

use FluxErp\Jobs\SyncAllMailAccountsJob;
use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use Illuminate\Support\Facades\Bus;

test('every mail account that can be reached is synced', function (): void {
    $imap = MailAccount::factory()->create(['protocol' => 'imap', 'host' => 'imap.example.com']);
    $msgraph = MailAccount::factory()->create(['protocol' => 'msgraph', 'host' => null]);
    $imapWithoutHost = MailAccount::factory()->create(['protocol' => 'imap', 'host' => null]);
    $withoutProtocol = MailAccount::factory()->create(['protocol' => null, 'host' => null]);

    Bus::fake();

    (new SyncAllMailAccountsJob())->handle();

    $synced = [];
    Bus::assertDispatched(
        SyncMailAccountJob::class,
        function (SyncMailAccountJob $job) use (&$synced): bool {
            $synced[] = (new ReflectionProperty($job, 'mailAccount'))->getValue($job)->getKey();

            return true;
        }
    );

    expect($synced)->toEqualCanonicalizing([$imap->getKey(), $msgraph->getKey()])
        ->and($synced)->not->toContain($imapWithoutHost->getKey(), $withoutProtocol->getKey());
});
