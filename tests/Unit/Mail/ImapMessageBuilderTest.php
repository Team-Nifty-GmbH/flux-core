<?php

use Carbon\CarbonImmutable;
use FluxErp\Mail\ImapMessage;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;

test('can be instantiated from a mail folder', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder)->toBeInstanceOf(ImapMessageBuilder::class);
});

test('returns itself for chainable filter methods', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder->unseen())->toBe($builder)
        ->and($builder->seen())->toBe($builder)
        ->and($builder->newSince(100))->toBe($builder);
});

test('returns empty collection before fetch', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder->get())->toBeEmpty()
        ->and($builder->count())->toBe(0);
});

test('creates a communication from an imap message with integer uid', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    makeTestableBuilder($folder)
        ->pushMessage(makeImapMessage(uid: 42, messageId: '<create-uid@example.com>'))
        ->store();

    $this->assertDatabaseHas('communications', [
        'mail_account_id' => $mailAccount->getKey(),
        'mail_folder_id' => $folder->getKey(),
        'message_id' => '<create-uid@example.com>',
        'message_uid' => '42',
    ]);
});

test('updates an existing communication from an imap message with integer uid', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $communication = Communication::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'mail_folder_id' => $folder->getKey(),
        'message_id' => '<update-uid@example.com>',
        'message_uid' => '41',
        'communication_type_enum' => 'mail',
    ]);

    makeTestableBuilder($folder)
        ->pushMessage(makeImapMessage(uid: 42, messageId: '<update-uid@example.com>'))
        ->store();

    expect($communication->refresh()->message_uid)->toBe('42');
});

test('reports progress for each stored message', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $progress = [];
    makeTestableBuilder($folder)
        ->onProgress(function (int $processed, int $total) use (&$progress): void {
            $progress[] = [$processed, $total];
        })
        ->pushMessage(makeImapMessage(uid: 50, messageId: '<progress-1@example.com>'))
        ->pushMessage(makeImapMessage(uid: 51, messageId: '<progress-2@example.com>'))
        ->store();

    expect($progress)->toBe([[1, 2], [2, 2]]);
});

test('syncReadStatus reconciles db read status against the server unseen uids', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $base = [
        'mail_account_id' => $mailAccount->getKey(),
        'mail_folder_id' => $folder->getKey(),
        'communication_type_enum' => 'mail',
    ];

    // seen in db, not unseen on server -> stays seen
    $staysSeen = Communication::factory()->create($base + ['message_uid' => '41', 'is_seen' => true]);
    // seen in db, unseen on server -> becomes unseen
    $becomesUnseen = Communication::factory()->create($base + ['message_uid' => '42', 'is_seen' => true]);
    // unseen in db, no longer unseen on server -> becomes seen
    $becomesSeen = Communication::factory()->create($base + ['message_uid' => '43', 'is_seen' => false]);

    makeTestableBuilder($folder)
        ->setUnseenUids([42])
        ->syncReadStatus();

    expect($staysSeen->refresh()->is_seen)->toBeTrue()
        ->and($becomesUnseen->refresh()->is_seen)->toBeFalse()
        ->and($becomesSeen->refresh()->is_seen)->toBeTrue();
});

test('syncReadStatus leaves read status untouched when the unseen uids cannot be determined', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $base = [
        'mail_account_id' => $mailAccount->getKey(),
        'mail_folder_id' => $folder->getKey(),
        'communication_type_enum' => 'mail',
    ];

    $seen = Communication::factory()->create($base + ['message_uid' => '41', 'is_seen' => true]);
    $unseen = Communication::factory()->create($base + ['message_uid' => '42', 'is_seen' => false]);

    makeTestableBuilder($folder)
        ->setUnseenUids(null)
        ->syncReadStatus();

    expect($seen->refresh()->is_seen)->toBeTrue()
        ->and($unseen->refresh()->is_seen)->toBeFalse();
});

function makeTestableBuilder(MailFolder $folder): ImapMessageBuilder
{
    return new class($folder) extends ImapMessageBuilder
    {
        /** @var array<int, int>|null */
        public ?array $unseenUids = [];

        public function pushMessage(ImapMessage $message): static
        {
            $this->messages->push($message);

            return $this;
        }

        public function setUnseenUids(?array $uids): static
        {
            $this->unseenUids = $uids;

            return $this;
        }

        protected function resolveUnseenUids(): ?array
        {
            return $this->unseenUids;
        }
    };
}

function makeImapMessage(int $uid, string $messageId): ImapMessage
{
    return new ImapMessage(
        messageId: $messageId,
        uid: $uid,
        subject: 'Test Subject',
        from: 'sender@example.com',
        to: [],
        cc: [],
        bcc: [],
        textBody: 'Hello World',
        htmlBody: null,
        date: CarbonImmutable::now(),
        isSeen: true,
        flags: [],
        attachments: [],
    );
}
