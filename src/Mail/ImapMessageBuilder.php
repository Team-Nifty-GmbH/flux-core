<?php

namespace FluxErp\Mail;

use Closure;
use ErrorException;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Models\Communication;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Tag;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\GetMessagesFailedException;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Folder;

class ImapMessageBuilder
{
    protected bool $filterUnseen = false;

    protected bool $filterSeen = false;

    protected bool $fetchBody = true;

    protected ?int $sinceUid = null;

    protected ?Closure $progressCallback = null;

    protected int $progressProcessed = 0;

    /** @var Collection<int, ImapMessage> */
    protected Collection $messages;

    public function __construct(protected readonly MailFolder $folder)
    {
        $this->messages = new Collection();
    }

    public function unseen(): static
    {
        $this->filterUnseen = true;
        $this->filterSeen = false;

        return $this;
    }

    public function seen(): static
    {
        $this->filterSeen = true;
        $this->filterUnseen = false;

        return $this;
    }

    public function withBody(): static
    {
        $this->fetchBody = true;

        return $this;
    }

    public function withoutBody(): static
    {
        $this->fetchBody = false;

        return $this;
    }

    public function newSince(?int $uid): static
    {
        $this->sinceUid = $uid;

        return $this;
    }

    public function onProgress(?Closure $callback): static
    {
        $this->progressCallback = $callback;

        return $this;
    }

    public function reset(): static
    {
        $this->filterSeen = false;
        $this->filterUnseen = false;
        $this->fetchBody = true;
        $this->sinceUid = null;
        $this->progressProcessed = 0;
        $this->messages = new Collection();

        return $this;
    }

    public function fetch(): static
    {
        $imapFolder = $this->resolveImapFolder();

        if (! $imapFolder) {
            return $this;
        }

        $this->messages = new Collection();

        if (! is_null($this->sinceUid)) {
            $this->fetchNewMessages($imapFolder);
        }

        if ($this->filterUnseen || $this->filterSeen || is_null($this->sinceUid)) {
            $this->fetchFilteredMessages($imapFolder);
        }

        return $this;
    }

    public function fetchAndStore(): static
    {
        $this->overConnection(function (): void {
            $imapFolder = $this->resolveImapFolder();

            if (! $imapFolder) {
                return;
            }

            $onMessage = function (ImapMessage $imapMessage): void {
                $this->storeMessage($imapMessage);
            };

            if (! is_null($this->sinceUid)) {
                $this->fetchNewMessages($imapFolder, $onMessage);
            }

            if ($this->filterUnseen || $this->filterSeen || is_null($this->sinceUid)) {
                $this->fetchFilteredMessages($imapFolder, $onMessage);
            }
        });

        return $this;
    }

    public function store(): static
    {
        $total = $this->messages->count();

        foreach ($this->messages as $imapMessage) {
            $this->storeMessage($imapMessage);
            $this->progressCallback?->__invoke(++$this->progressProcessed, $total);
        }

        return $this;
    }

    public function syncReadStatus(): static
    {
        $unreadUids = $this->overConnection(fn (): ?array => $this->resolveUnseenUids());

        if (is_null($unreadUids)) {
            return $this;
        }

        resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $this->folder->mailAccount->getKey())
            ->where('mail_folder_id', $this->folder->getKey())
            ->whereIntegerNotInRaw('message_uid', $unreadUids)
            ->where('is_seen', false)
            ->each(
                fn (Communication $message) => UpdateCommunication::make([
                    'id' => $message->getKey(),
                    'is_seen' => true,
                ])
                    ->validate()
                    ->execute()
            );

        resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $this->folder->mailAccount->getKey())
            ->where('mail_folder_id', $this->folder->getKey())
            ->whereIntegerInRaw('message_uid', $unreadUids)
            ->where('is_seen', true)
            ->each(
                fn (Communication $message) => UpdateCommunication::make([
                    'id' => $message->getKey(),
                    'is_seen' => false,
                ])
                    ->validate()
                    ->execute()
            );

        return $this;
    }

    /** @return Collection<int, ImapMessage> */
    public function get(): Collection
    {
        return $this->messages;
    }

    public function count(): int
    {
        return $this->messages->count();
    }

    /**
     * Run one piece of work against the mail server and repeat it once on a
     * fresh connection when the old one dies underneath it.
     *
     * The server closes an idle or long running connection on its own, and the
     * next write into that stream raises a PHP warning that Laravel turns into
     * an ErrorException, which ends the whole sync run. Repeating is safe
     * because storeMessage() looks a message up by its message id and updates
     * the existing row rather than writing a second one.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $operation
     * @return TReturn
     */
    protected function overConnection(Closure $operation): mixed
    {
        try {
            return $operation();
        } catch (Throwable $exception) {
            if (! $this->isLostConnection($exception)) {
                throw $exception;
            }
        }

        // The account caches its client, so the retry only reaches a live stream
        // once that cache is cleared.
        try {
            $this->reconnect();
        } catch (Throwable) {
            // The server is not answering at all. Report the lost connection
            // rather than the failed dial, which says nothing about the cause.
            throw $exception;
        }

        $this->progressProcessed = 0;

        return $operation();
    }

    /**
     * Build a new connection for the retry. Its own seam so a test can drive the
     * retry without an IMAP server behind it.
     */
    protected function reconnect(): void
    {
        $this->folder->mailAccount->reconnectImapClient();
    }

    /**
     * Whether the server took the connection away, as opposed to any other
     * failure. Everything else is left alone rather than retried blindly.
     */
    protected function isLostConnection(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionFailedException
            || $exception instanceof GetMessagesFailedException
        ) {
            return true;
        }

        if (! $exception instanceof ErrorException && ! $exception instanceof RuntimeException) {
            return false;
        }

        // A stream failure only counts when it happened inside the imap client.
        // The same words turn up in unrelated failures, and retrying one of
        // those would repeat work that never touched the connection.
        if (! str_contains($exception->getFile(), 'webklex/php-imap')) {
            return false;
        }

        // php-imap reports the loss as a failed stream write, and PHP itself as
        // a broken pipe or a reset by the peer.
        return (bool) preg_match(
            '/broken pipe|connection closed|connection reset|fwrite|fread/i',
            $exception->getMessage()
        );
    }

    protected function resolveImapFolder(): ?Folder
    {
        $client = $this->folder
            ->mailAccount
            ->getImapClient();

        if (! $client) {
            return null;
        }

        return $client->getFolderByPath($this->folder->slug, utf7: true);
    }

    /**
     * Return the UIDs of the currently unseen messages on the server, or null
     * when the state could not be determined.
     *
     * Uses a plain IMAP SEARCH (a single round-trip returning only UIDs) instead
     * of fetching full message objects, so the read-status reconciliation no
     * longer scales with the number of unseen mails times a per-message fetch.
     *
     * Returns null (not an empty array) when the folder cannot be resolved or
     * the search fails, so the caller can skip reconciliation instead of
     * treating the failure as "nothing is unseen".
     *
     * @return array<int, int>|null
     */
    protected function resolveUnseenUids(): ?array
    {
        $imapFolder = $this->resolveImapFolder();

        if (! $imapFolder) {
            return null;
        }

        try {
            return $imapFolder->messages()
                ->setFetchBody(false)
                ->leaveUnread()
                ->unseen()
                ->since($this->folder->mailAccount->created_at)
                ->search()
                ->map(fn (mixed $uid): int => (int) $uid)
                ->values()
                ->toArray();
        } catch (ResponseException) {
            return null;
        }
    }

    protected function fetchNewMessages(Folder $imapFolder, ?Closure $onMessage = null): void
    {
        try {
            $query = $imapFolder->messages()
                ->setFetchBody(false)
                ->leaveUnread()
                ->getByUidGreater($this->sinceUid);
        } catch (ResponseException) {
            return;
        }

        $page = 0;
        do {
            $page++;
            $messages = $query->paginate(100, $page);

            foreach ($messages as $message) {
                $imapMessage = ImapMessage::fromImapMessage($message, $this->fetchBody);

                if ($onMessage) {
                    $onMessage($imapMessage);
                    $this->progressCallback?->__invoke(++$this->progressProcessed, $messages->total());
                } else {
                    $this->messages->push($imapMessage);
                }
            }
        } while ($page !== $messages->lastPage());
    }

    protected function fetchFilteredMessages(Folder $imapFolder, ?Closure $onMessage = null): void
    {
        try {
            $query = $imapFolder->messages()
                ->setFetchBody(false)
                ->leaveUnread()
                ->since($this->folder->mailAccount->created_at);

            if ($this->filterUnseen) {
                $query->unseen();
            } elseif ($this->filterSeen) {
                $query->seen();
            }
        } catch (ResponseException) {
            return;
        }

        $page = 0;
        do {
            $page++;
            $messages = $query->paginate(100, $page);

            foreach ($messages as $message) {
                $imapMessage = ImapMessage::fromImapMessage($message, $this->fetchBody);

                if ($onMessage) {
                    $onMessage($imapMessage);
                    $this->progressCallback?->__invoke(++$this->progressProcessed, $messages->total());
                } else {
                    $this->messages->push($imapMessage);
                }
            }
        } while ($page !== $messages->lastPage());
    }

    protected function storeMessage(ImapMessage $imapMessage): void
    {
        $existing = resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $this->folder->mailAccount->getKey())
            ->where('message_id', $imapMessage->messageId)
            ->first();

        if (! $existing) {
            $this->createMessage($imapMessage);
        } else {
            UpdateCommunication::make([
                'id' => $existing->getKey(),
                'mail_folder_id' => $this->folder->getKey(),
                'message_uid' => (string) $imapMessage->uid,
                'communication_type_enum' => 'mail',
                'is_seen' => $imapMessage->isSeen,
            ])
                ->validate()
                ->execute();
        }
    }

    protected function createMessage(ImapMessage $imapMessage): void
    {
        $tagIds = $this->resolveTagIds($imapMessage->flags);

        CreateMailMessage::make([
            'mail_account_id' => $this->folder->mailAccount->getKey(),
            'mail_folder_id' => $this->folder->getKey(),
            'message_id' => $imapMessage->messageId,
            'message_uid' => (string) $imapMessage->uid,
            'from' => $imapMessage->from,
            'to' => $imapMessage->to,
            'cc' => $imapMessage->cc,
            'bcc' => $imapMessage->bcc,
            'communication_type_enum' => 'mail',
            'date' => $imapMessage->date->toDateTimeString(),
            'subject' => $imapMessage->subject,
            'text_body' => $imapMessage->textBody,
            'html_body' => $imapMessage->htmlBody,
            'is_seen' => $imapMessage->isSeen,
            'tags' => $tagIds,
            'attachments' => $imapMessage->attachments,
        ])
            ->validate()
            ->execute();
    }

    protected function resolveTagIds(array $flags): array
    {
        $tagIds = [];
        $type = morph_alias(Communication::class);
        $existingTags = resolve_static(Tag::class, 'query')
            ->whereIn('name', $flags)
            ->where('type', $type)
            ->pluck('id', 'name')
            ->toArray();

        foreach ($flags as $flag) {
            if ($existingTag = data_get($existingTags, $flag)) {
                $tagIds[] = $existingTag;
            } else {
                $tagIds[] = CreateTag::make(['name' => $flag, 'type' => $type])
                    ->validate()
                    ->execute()
                    ->getKey();
            }
        }

        return $tagIds;
    }
}
