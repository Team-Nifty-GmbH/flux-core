<?php

use Carbon\Carbon;
use FluxErp\Mail\ImapMailSyncDriver;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Query\WhereQuery;
use Webklex\PHPIMAP\Support\MessageCollection;

function fakeImapMailbox(MailAccount $mailAccount, int $uidValidity, int $firstUid, ?Closure $since = null): void
{
    $message = Mockery::mock(Message::class);
    $message->shouldReceive('getUid')->andReturn($firstUid);

    $query = Mockery::mock(WhereQuery::class);
    $query->shouldReceive('all')->andReturnSelf();
    $query->shouldReceive('since')
        ->with(Mockery::on($since ?? fn (): bool => true))
        ->andReturnSelf();
    $query->shouldReceive('limit')->andReturnSelf();
    $query->shouldReceive('get')->andReturn(new MessageCollection([$message]));

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('examine')->andReturn(['uidvalidity' => $uidValidity, 'uidnext' => $firstUid + 1]);
    $imapFolder->shouldReceive('messages')->andReturn($query);

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('getFolderByPath')->andReturn($imapFolder);

    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($mailAccount, $client);
}

function recordImapSync(): object
{
    $recorder = new class()
    {
        public ?int $sinceUid = null;
    };

    app()->bind(
        ImapMessageBuilder::class,
        fn ($app, array $parameters) => new class($parameters['folder'], $recorder) extends ImapMessageBuilder
        {
            public function __construct(MailFolder $folder, protected object $recorder)
            {
                parent::__construct($folder);
            }

            public function newSince(?int $uid): static
            {
                $this->recorder->sinceUid = $uid;

                return $this;
            }

            public function fetchAndStore(): static
            {
                return $this;
            }

            public function syncReadStatus(): static
            {
                return $this;
            }
        }
    );

    return $recorder;
}

function storeImapMail(MailFolder $mailFolder, string $uid, string $date): Communication
{
    return Communication::factory()->create([
        'mail_account_id' => $mailFolder->mail_account_id,
        'mail_folder_id' => $mailFolder->getKey(),
        'communication_type_enum' => 'mail',
        'message_uid' => $uid,
        'date' => $date,
    ]);
}

test('a changed uidvalidity resumes at the newest stored mail and drops the stale uids', function (): void {
    $mailFolder = MailFolder::factory()->for(MailAccount::factory())->create(['uid_validity' => 1]);
    $older = storeImapMail($mailFolder, '6000', '2026-10-01 09:00:00');
    $newest = storeImapMail($mailFolder, '6695', '2026-10-08 10:00:00');

    fakeImapMailbox(
        $mailFolder->mailAccount,
        uidValidity: 2,
        firstUid: 2280,
        since: fn (mixed $date): bool => Carbon::parse($date)->toDateString() === '2026-10-08'
    );
    $recorder = recordImapSync();

    app(ImapMailSyncDriver::class)->syncMessages($mailFolder);

    expect($recorder->sinceUid)->toBe(2279)
        ->and($older->fresh()->message_uid)->toBeNull()
        ->and($newest->fresh()->message_uid)->toBeNull()
        ->and($mailFolder->fresh()->uid_validity)->toBe(2);
});

test('an unchanged uidvalidity continues after the highest stored uid', function (): void {
    $mailFolder = MailFolder::factory()->for(MailAccount::factory())->create(['uid_validity' => 2]);
    $newest = storeImapMail($mailFolder, '6695', '2026-10-08 10:00:00');

    fakeImapMailbox($mailFolder->mailAccount, uidValidity: 2, firstUid: 2280);
    $recorder = recordImapSync();

    app(ImapMailSyncDriver::class)->syncMessages($mailFolder);

    expect($recorder->sinceUid)->toBe(6695)
        ->and($newest->fresh()->message_uid)->toBe('6695')
        ->and($mailFolder->fresh()->uid_validity)->toBe(2);
});

test('the first sync of a folder stores its uidvalidity and keeps the stored uids', function (): void {
    $mailFolder = MailFolder::factory()->for(MailAccount::factory())->create(['uid_validity' => null]);
    $newest = storeImapMail($mailFolder, '6695', '2026-10-08 10:00:00');

    fakeImapMailbox($mailFolder->mailAccount, uidValidity: 2, firstUid: 2280);
    $recorder = recordImapSync();

    app(ImapMailSyncDriver::class)->syncMessages($mailFolder);

    expect($recorder->sinceUid)->toBe(6695)
        ->and($newest->fresh()->message_uid)->toBe('6695')
        ->and($mailFolder->fresh()->uid_validity)->toBe(2);
});

test('a changed uidvalidity ignores and clears the uid of a skipped mail', function (): void {
    $mailFolder = MailFolder::factory()->for(MailAccount::factory())->create([
        'uid_validity' => 1,
        'last_skipped_uid' => 9000,
    ]);
    storeImapMail($mailFolder, '6695', '2026-10-08 10:00:00');

    fakeImapMailbox($mailFolder->mailAccount, uidValidity: 2, firstUid: 2280);
    $recorder = recordImapSync();

    app(ImapMailSyncDriver::class)->syncMessages($mailFolder);

    expect($recorder->sinceUid)->toBe(2279)
        ->and($mailFolder->fresh()->last_skipped_uid)->toBeNull();
});
