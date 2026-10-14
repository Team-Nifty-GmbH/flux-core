<?php

use Carbon\CarbonImmutable;
use FluxErp\Mail\ImapMessage;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Rulesets\Communication\CreateCommunicationRuleset;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Validation\ValidationException;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Query\WhereQuery;
use Webklex\PHPIMAP\Support\AttachmentCollection;
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
    $broken->shouldReceive('parseBody')->andReturnSelf();
    $broken->shouldReceive('getAttachments')->andReturn(new AttachmentCollection());
    $broken->shouldReceive('getMessageId->toString')->andThrow(new RuntimeException('unparsable message'));

    $intact = Mockery::mock(Message::class);
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
    ?string $fromMail = null,
): Message {
    $message = Mockery::mock(Message::class);
    $message->shouldReceive('parseBody')->andReturnSelf();
    $message->shouldReceive('getAttachments')->andReturn(new AttachmentCollection());
    $message->shouldReceive('getMessageId->toString')->andReturn($messageId ?? '<' . $uid . '@example.com>');
    $message->shouldReceive('getUid')->andReturn($uid);
    $message->shouldReceive('getSubject->toString')->andReturn($subject ?? 'Mail ' . $uid);
    $message->shouldReceive('getFrom')->andReturn([(object) ['full' => $from, 'mail' => $fromMail]]);
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
    $query->shouldReceive('getByUidGreater')->andReturnUsing(fn (): MessageCollection => new MessageCollection($messages));

    $imapFolder = Mockery::mock(Folder::class);
    $imapFolder->shouldReceive('messages')->andReturn($query);

    return $imapFolder;
}

test('stores an overlong message id shortened and finds it again on the next run', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $longId = '<' . str_repeat('a', 300) . '@example.com>';
    $imapFolder = makeImapFolderServingNew([makeReadableImapMessage(11, messageId: $longId)]);

    makeTestableBuilder($folder)->setImapFolder($imapFolder)->newSince(10)->fetchAndStore();
    makeTestableBuilder($folder)->setImapFolder($imapFolder)->newSince(10)->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->pluck('message_id')->all())
        ->toBe([mb_substr($longId, 0, 214) . '.' . sha1($longId)]);
});

test('stores an overlong subject and sender cut to 255 characters', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([
            makeReadableImapMessage(11, subject: str_repeat('s', 300), from: str_repeat('f', 300)),
        ]))
        ->newSince(10)
        ->fetchAndStore();

    $stored = Communication::query()->where('mail_folder_id', $folder->getKey())->sole();

    expect($stored->subject)->toBe(str_repeat('s', 255))
        ->and($stored->from)->toBe(str_repeat('f', 255));
});

test('reports a message that fails validation and still imports the following ones', function (): void {
    Exceptions::fake();
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    $rejecting = new class() extends CreateCommunicationRuleset
    {
        public function rules(): array
        {
            return array_merge(parent::rules(), ['message_id' => 'nullable|string|max:255|not_in:<11@example.com>']);
        }
    };
    app()->bind(CreateCommunicationRuleset::class, $rejecting::class);

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([
            makeReadableImapMessage(11),
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

test('decodes an overlong mime encoded subject before cutting it', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $subject = str_repeat('ä', 150);

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([
            makeReadableImapMessage(11, subject: '=?UTF-8?B?' . base64_encode($subject) . '?='),
        ]))
        ->newSince(10)
        ->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->sole()->subject)->toBe($subject);
});

test('keeps the sender address when a long display name exceeds 255 characters', function (): void {
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();

    makeTestableBuilder($folder)
        ->setImapFolder(makeImapFolderServingNew([
            makeReadableImapMessage(
                11,
                from: str_repeat('N', 300) . ' <sender@example.com>',
                fromMail: 'sender@example.com',
            ),
        ]))
        ->newSince(10)
        ->fetchAndStore();

    expect(Communication::query()->where('mail_folder_id', $folder->getKey())->sole()->from_mail)
        ->toBe('sender@example.com');
});

test('removes the temporary attachment files of a message that fails validation', function (): void {
    Exceptions::fake();
    $folder = MailAccount::factory()->has(MailFolder::factory())->create()->mailFolders->first();
    $tempPath = tempnam(sys_get_temp_dir(), 'imap_');
    file_put_contents($tempPath, 'attachment');

    $rejecting = new class() extends CreateCommunicationRuleset
    {
        public function rules(): array
        {
            return array_merge(parent::rules(), ['message_id' => 'nullable|string|max:255|not_in:<rejected@example.com>']);
        }
    };
    app()->bind(CreateCommunicationRuleset::class, $rejecting::class);

    makeTestableBuilder($folder)
        ->pushMessage(new ImapMessage(
            messageId: '<rejected@example.com>',
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
