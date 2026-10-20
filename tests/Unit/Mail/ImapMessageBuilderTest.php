<?php

use Carbon\CarbonImmutable;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Mail\ImapMailSyncDriver;
use FluxErp\Mail\ImapMessage;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Query\WhereQuery;
use Webklex\PHPIMAP\Support\AttachmentCollection;
use Webklex\PHPIMAP\Support\FolderCollection;
use Webklex\PHPIMAP\Support\MessageCollection;

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

test('reports an existing message that fails validation and still stores the following ones', function (): void {
    Exceptions::fake();
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $communication = Communication::factory()->create([
        'mail_account_id' => $mailAccount->getKey(),
        'mail_folder_id' => $folder->getKey(),
        'message_id' => '<rejected-update@example.com>',
        'message_uid' => '41',
        'communication_type_enum' => 'mail',
    ]);

    UpdateCommunication::validating(fn () => throw ValidationException::withMessages(['id' => 'rejected']));

    makeTestableBuilder($folder)
        ->pushMessage(makeImapMessage(uid: 42, messageId: '<rejected-update@example.com>'))
        ->pushMessage(makeImapMessage(uid: 43, messageId: '<after-rejected-update@example.com>'))
        ->store();

    expect($communication->refresh()->message_uid)->toBe('41')
        ->and(Communication::query()->where('message_id', '<after-rejected-update@example.com>')->exists())->toBeTrue();
    Exceptions::assertReported(
        fn (RuntimeException $exception): bool => $exception->getPrevious() instanceof ValidationException
    );
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

test('keeps importing a folder when one message cannot be read', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $broken = Mockery::mock(Message::class);
    $broken->shouldReceive('getSize')->andReturn(1024);
    $broken->shouldReceive('parseBody')->andReturnSelf();
    $broken->shouldReceive('getAttachments')->andReturn(new AttachmentCollection());
    $broken->shouldReceive('getMessageId->toString')->andThrow(new RuntimeException('unparsable message'));

    $intact = Mockery::mock(Message::class);
    $intact->shouldReceive('getSize')->andReturn(1024);
    $intact->shouldReceive('parseBody')->andReturnSelf();
    $intact->shouldReceive('getAttachments')->andReturn(new AttachmentCollection());
    $intact->shouldReceive('getMessageId->toString')->andReturn('<intact@example.com>');
    $intact->shouldReceive('getUid')->andReturn(43);
    $intact->shouldReceive('getSubject->toString')->andReturn('Anfrage');
    $intact->shouldReceive('getFrom')->andReturn([(object) ['full' => 'sender@example.com']]);
    $intact->shouldReceive('getTo->toArray')->andReturn([]);
    $intact->shouldReceive('getCc->toArray')->andReturn([]);
    $intact->shouldReceive('getBcc->toArray')->andReturn([]);
    $intact->shouldReceive('getTextBody')->andReturn('Hello');
    $intact->shouldReceive('getHtmlBody')->andReturn('<p>Hello</p>');
    $intact->shouldReceive('getDate->toDate')->andReturn(new DateTime('2026-01-30 12:00:00'));
    $intact->shouldReceive('hasFlag')->andReturn(false);
    $intact->shouldReceive('getFlags->toArray')->andReturn([]);

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServing([$broken, $intact]))
        ->fetchAndStore();

    $this->assertDatabaseHas('communications', [
        'mail_account_id' => $mailAccount->getKey(),
        'message_id' => '<intact@example.com>',
    ]);
});

function makeTestableBuilder(MailFolder $folder): ImapMessageBuilder
{
    return new class($folder) extends ImapMessageBuilder
    {
        /** @var array<int, int>|null */
        public ?array $unseenUids = [];

        public ?Folder $imapFolder = null;

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

        public function setImapFolder(Folder $folder): static
        {
            $this->imapFolder = $folder;

            return $this;
        }

        protected function resolveImapFolder(): ?Folder
        {
            return $this->imapFolder;
        }

        protected function resolveUnseenUids(): ?array
        {
            return $this->unseenUids;
        }
    };
}

function makeImapFolderServing(array $messages): Folder
{
    $query = Mockery::mock(WhereQuery::class);
    $query->shouldReceive('setFetchBody')->andReturnSelf();
    $query->shouldReceive('leaveUnread')->andReturnSelf();
    $query->shouldReceive('since')->andReturnSelf();
    $query->shouldReceive('paginate')->andReturn(
        new LengthAwarePaginator($messages, count($messages), 100, 1)
    );

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('messages')->andReturn($query);

    return $imapFolder;
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

/**
 * A stream failure as it arrives from the imap client: PHP raises the warning
 * inside the package, and Laravel turns it into an ErrorException carrying that
 * file.
 */
function makeStreamFailure(string $message = 'fwrite(): SSL: Broken pipe'): ErrorException
{
    return new ErrorException(
        $message,
        0,
        E_WARNING,
        '/app/vendor/webklex/php-imap/src/Connection/Protocols/ImapProtocol.php',
        469,
    );
}

/**
 * The mail server drops a long running connection on its own. The next write
 * into that stream raises a PHP warning which Laravel turns into an
 * ErrorException, and that used to end the whole sync run.
 */
function makeReconnectingBuilder(
    MailFolder $folder,
    Throwable $failure,
    int $failures = 1,
    ?Throwable $reconnectFailure = null,
): ImapMessageBuilder {
    return new class($folder, $failure, $failures, $reconnectFailure) extends ImapMessageBuilder
    {
        public int $attempts = 0;

        public int $reconnects = 0;

        public function __construct(
            MailFolder $folder,
            private readonly Throwable $failure,
            private int $failures,
            private readonly ?Throwable $reconnectFailure,
        ) {
            parent::__construct($folder);
        }

        public function run(): mixed
        {
            return $this->overConnection(function (): string {
                $this->attempts++;

                if ($this->failures-- > 0) {
                    throw $this->failure;
                }

                return 'done';
            });
        }

        protected function reconnect(): void
        {
            $this->reconnects++;

            if ($this->reconnectFailure) {
                throw $this->reconnectFailure;
            }
        }
    };
}

test('repeats the work on a fresh connection when the server closed the old one', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    $builder = makeReconnectingBuilder($folder, makeStreamFailure());

    expect($builder->run())->toBe('done')
        ->and($builder->attempts)->toBe(2)
        ->and($builder->reconnects)->toBe(1);
});

test('asks the account for a new client instead of reusing the dead one', function (): void {
    $account = new class() extends MailAccount
    {
        public int $connects = 0;

        public function connect(): ?Client
        {
            $this->connects++;

            return null;
        }
    };

    $account->reconnectImapClient();

    expect($account->connects)->toBe(1);
});

test('lets an unrelated failure through instead of repeating it', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    $builder = makeReconnectingBuilder($folder, new RuntimeException('mailbox does not exist'));

    expect(fn () => $builder->run())->toThrow(RuntimeException::class, 'mailbox does not exist')
        ->and($builder->attempts)->toBe(1)
        ->and($builder->reconnects)->toBe(0);
});

test('gives up after one repeat rather than looping', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    $builder = makeReconnectingBuilder($folder, makeStreamFailure(), failures: 2);

    expect(fn () => $builder->run())->toThrow(ErrorException::class)
        ->and($builder->attempts)->toBe(2);
});

test('reports the lost connection rather than the failed dial', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    $builder = makeReconnectingBuilder(
        $folder,
        makeStreamFailure(),
        reconnectFailure: new RuntimeException('could not reach the mail server'),
    );

    expect(fn () => $builder->run())->toThrow(ErrorException::class, 'fwrite(): SSL: Broken pipe')
        ->and($builder->attempts)->toBe(1);
});

test('leaves a stream failure alone that did not come from the imap client', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    // Same words, raised while writing an attachment to disk rather than to the
    // connection. Repeating that would redo work the server never saw.
    $builder = makeReconnectingBuilder(
        $folder,
        new ErrorException('fwrite(): write of 8192 bytes failed', 0, E_WARNING, __FILE__, __LINE__),
    );

    expect(fn () => $builder->run())->toThrow(ErrorException::class)
        ->and($builder->attempts)->toBe(1)
        ->and($builder->reconnects)->toBe(0);
});

test('lets go of every new message once it is stored', function (): void {
    $mailAccount = MailAccount::factory()
        ->has(MailFolder::factory())
        ->create();
    $folder = $mailAccount->mailFolders->first();

    $newMessages = new MessageCollection(
        array_map(fn (int $uid): Message => makeReadableImapMessage($uid), [11, 12, 13])
    );

    $query = Mockery::mock(WhereQuery::class);
    $query->shouldReceive('setFetchBody')->andReturnSelf();
    $query->shouldReceive('leaveUnread')->andReturnSelf();
    $query->shouldReceive('limit')->andReturnSelf();
    $query->shouldReceive('getByUidGreater')->with(10)->andReturn($newMessages);

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('messages')->andReturn($query);

    $stillHeld = [];

    makeTestableBuilder($folder)
        ->setImapFolder($imapFolder)
        ->newSince(10)
        ->onProgress(function (int $processed, int $total) use (&$stillHeld, $newMessages): void {
            $stillHeld[] = $newMessages->count();
        })
        ->fetchAndStore();

    expect($stillHeld)->toBe([2, 1, 0])
        ->and(Communication::query()->where('mail_folder_id', $folder->getKey())->count())->toBe(3);
});

function makeReadableImapMessage(
    int $uid,
    ?string $messageId = null,
    ?string $subject = null,
    string $from = 'sender@example.com',
    int $size = 1024,
): Message {
    $message = Mockery::mock(Message::class);
    $message->shouldReceive('getSize')->andReturn($size);
    $message->shouldReceive('parseBody')->andReturnSelf();
    $message->shouldReceive('getAttachments')->andReturn(new AttachmentCollection());
    $message->shouldReceive('getMessageId->toString')->andReturn($messageId ?? '<' . $uid . '@example.com>');
    $message->shouldReceive('getUid')->andReturn($uid);
    $message->shouldReceive('getSubject->toString')->andReturn($subject ?? 'Mail ' . $uid);
    $message->shouldReceive('getFrom')->andReturn([(object) ['full' => $from]]);
    $message->shouldReceive('getTo->toArray')->andReturn([]);
    $message->shouldReceive('getCc->toArray')->andReturn([]);
    $message->shouldReceive('getBcc->toArray')->andReturn([]);
    $message->shouldReceive('getTextBody')->andReturn('Hello');
    $message->shouldReceive('getHtmlBody')->andReturn('<p>Hello</p>');
    $message->shouldReceive('getDate->toDate')->andReturn(new DateTime('2026-01-30 12:00:00'));
    $message->shouldReceive('hasFlag')->andReturn(false);
    $message->shouldReceive('getFlags->toArray')->andReturn([]);

    return $message;
}

function makeImapFolderServingNew(array $messages): Folder
{
    $query = Mockery::mock(WhereQuery::class);
    $query->shouldReceive('setFetchBody')->andReturnSelf();
    $query->shouldReceive('leaveUnread')->andReturnSelf();
    $query->shouldReceive('limit')->andReturnSelf();
    $query->shouldReceive('getByUidGreater')->andReturnUsing(fn (): MessageCollection => new MessageCollection($messages));

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('messages')->andReturn($query);

    return $imapFolder;
}

test('stores a message id of 900 characters in full and finds it again on the next run', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $longId = '<' . str_repeat('a', 900) . '@example.com>';
    $imapFolder = makeImapFolderServingNew([makeReadableImapMessage(11, messageId: $longId)]);

    makeTestableBuilder($folder)->setImapFolder($imapFolder)->newSince(10)->fetchAndStore();
    makeTestableBuilder($folder)->setImapFolder($imapFolder)->newSince(10)->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->pluck('message_id')->all())
        ->toBe([$longId]);
});

test('stores a long subject and sender in full', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $from = str_repeat('N', 400) . ' <sender@example.com>';

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([
            makeReadableImapMessage(11, subject: str_repeat('s', 400), from: $from),
        ]))
        ->newSince(10)
        ->fetchAndStore();

    $stored = Communication::query()->where('mail_folder_id', $folder->getKey())->sole();

    expect($stored->subject)->toBe(str_repeat('s', 400))
        ->and($stored->from)->toBe($from);
});

test('reports a message id longer than 998 characters and still imports the following ones', function (): void {
    Exceptions::fake();
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([
            makeReadableImapMessage(11, messageId: '<' . str_repeat('a', 998) . '>'),
            makeReadableImapMessage(12),
            makeReadableImapMessage(13),
        ]))
        ->newSince(10)
        ->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->orderBy('message_uid')->pluck('message_uid')->all())
        ->toBe(['12', '13']);
    Exceptions::assertReported(
        fn (RuntimeException $exception): bool => $exception->getPrevious() instanceof ValidationException
    );
});

test('removes the temporary attachment files of a message that fails validation', function (): void {
    Exceptions::fake();
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $tempPath = tempnam(sys_get_temp_dir(), 'imap_');
    file_put_contents($tempPath, 'attachment');

    makeTestableBuilder($folder)
        ->pushMessage(new ImapMessage(
            messageId: '<' . str_repeat('a', 998) . '>',
            uid: 11,
            subject: 'Rejected',
            from: 'sender@example.com',
            to: [],
            cc: [],
            bcc: [],
            textBody: 'Hello',
            htmlBody: null,
            date: CarbonImmutable::now(),
            isSeen: true,
            flags: [],
            attachments: [['file_name' => 'a.txt', 'mime_type' => 'text/plain', 'name' => 'a.txt', 'media' => $tempPath]],
        ))
        ->store();

    expect(file_exists($tempPath))->toBeFalse();
});

test('reports a rejected message once and starts the next sync after it', function (): void {
    Exceptions::fake();
    $folder = MailFolder::factory()->for(MailAccount::factory())->create(['uid_validity' => 2]);
    Communication::factory()->create([
        'mail_account_id' => $folder->mail_account_id,
        'mail_folder_id' => $folder->getKey(),
        'communication_type_enum' => 'mail',
        'message_uid' => '10',
    ]);

    $query = Mockery::mock(WhereQuery::class);
    $query->shouldReceive('setFetchBody', 'leaveUnread', 'unseen', 'since', 'limit')->andReturnSelf();
    $query->shouldReceive('search')->andReturn(collect());
    $query->shouldReceive('getByUidGreater')->andReturnUsing(
        fn (int $uid): MessageCollection => new MessageCollection(
            $uid < 11 ? [makeReadableImapMessage(11, messageId: '<' . str_repeat('a', 998) . '>')] : []
        )
    );

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('examine')->andReturn(['uidvalidity' => 2, 'uidnext' => 12]);
    $imapFolder->shouldReceive('messages')->andReturn($query);
    $imapFolder->path = $folder->slug;

    $client = Mockery::mock(Client::class);
    $client->shouldReceive('getFolders')->andReturn(new FolderCollection([$imapFolder]));
    (new ReflectionProperty(MailAccount::class, 'imapClient'))->setValue($folder->mailAccount, $client);

    app(ImapMailSyncDriver::class)->syncMessages($folder);
    $reloaded = $folder->fresh();
    $reloaded->setRelation('mailAccount', $folder->mailAccount);
    app(ImapMailSyncDriver::class)->syncMessages($reloaded);

    Exceptions::assertReportedCount(1);
    expect($reloaded->last_skipped_uid)->toBe(11);
});

test('skips and reports a message above the size limit without loading its body', function (): void {
    Exceptions::fake();
    config(['flux.mail.max_message_size' => 1024 * 1024]);
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $oversized = makeReadableImapMessage(11, size: 1024 * 1024 + 1);

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([$oversized, makeReadableImapMessage(12)]))
        ->newSince(10)
        ->fetchAndStore();

    $oversized->shouldNotHaveReceived('parseBody');
    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->pluck('message_uid')->all())
        ->toBe(['12'])
        ->and($folder->fresh()->last_skipped_uid)->toBe(11);
    Exceptions::assertReported(fn (RuntimeException $exception): bool => str_contains($exception->getMessage(), '11'));
});

test('fetches new messages page by page after the last uid it saw', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $onServer = [11, 12, 15];
    $requested = [];

    $query = Mockery::mock(WhereQuery::class);
    $query->shouldReceive('setFetchBody', 'leaveUnread')->andReturnSelf();
    $query->shouldReceive('limit')->with(2)->andReturnSelf();
    $query->shouldReceive('getByUidGreater')->andReturnUsing(
        function (int $uid) use (&$requested, $onServer): MessageCollection {
            $requested[] = $uid;
            $uids = array_slice(array_values(array_filter($onServer, fn (int $id): bool => $id > $uid)), 0, 2);

            return new MessageCollection(array_map(fn (int $id): Message => makeReadableImapMessage($id), $uids));
        }
    );

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('messages')->andReturn($query);

    $builder = makeTestableBuilder($folder)->setImapFolder($imapFolder)->newSince(10);
    (new ReflectionProperty(ImapMessageBuilder::class, 'pageSize'))->setValue($builder, 2);
    $builder->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->orderBy('message_uid')->pluck('message_uid')->all())
        ->toBe(['11', '12', '15'])
        ->and($requested)->toBe([10, 12]);
});

test('imports every message when no size limit is set', function (): void {
    config(['flux.mail.max_message_size' => 0]);
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([makeReadableImapMessage(11, size: 500 * 1024 * 1024)]))
        ->newSince(10)
        ->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->pluck('message_uid')->all())
        ->toBe(['11']);
});
