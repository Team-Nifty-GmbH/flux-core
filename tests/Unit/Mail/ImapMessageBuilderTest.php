<?php

use Carbon\CarbonImmutable;
use FluxErp\Mail\ImapMessage;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;

it('can be instantiated from a mail folder', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder)->toBeInstanceOf(ImapMessageBuilder::class);
});

it('returns itself for chainable filter methods', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder->unseen())->toBe($builder)
        ->and($builder->seen())->toBe($builder)
        ->and($builder->newSince(100))->toBe($builder);
});

it('returns empty collection before fetch', function (): void {
    $folder = new MailFolder();
    $builder = new ImapMessageBuilder($folder);

    expect($builder->get())->toBeEmpty()
        ->and($builder->count())->toBe(0);
});

it('creates a communication from an imap message with integer uid', function (): void {
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

it('updates an existing communication from an imap message with integer uid', function (): void {
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

it('reports progress for each stored message', function (): void {
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

function makeTestableBuilder(MailFolder $folder): ImapMessageBuilder
{
    return new class($folder) extends ImapMessageBuilder
    {
        public function pushMessage(ImapMessage $message): static
        {
            $this->messages->push($message);

            return $this;
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
