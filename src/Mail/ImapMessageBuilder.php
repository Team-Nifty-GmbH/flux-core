<?php

namespace FluxErp\Mail;

use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Models\Communication;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Tag;
use Illuminate\Support\Collection;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Folder;

class ImapMessageBuilder
{
    protected bool $filterUnseen = false;

    protected bool $filterSeen = false;

    protected ?int $sinceUid = null;

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

    public function newSince(?int $uid): static
    {
        $this->sinceUid = $uid;

        return $this;
    }

    public function reset(): static
    {
        $this->filterSeen = false;
        $this->filterUnseen = false;
        $this->sinceUid = null;
        $this->messages = new Collection();

        return $this;
    }

    public function fetch(): static
    {
        $client = $this->folder
            ->mailAccount
            ->getImapClient();

        if (! $client) {
            return $this;
        }

        $imapFolder = $client
            ->getFolders(false, $this->folder->slug)
            ->first();

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

    public function store(): static
    {
        foreach ($this->messages as $imapMessage) {
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
                    'message_uid' => $imapMessage->uid,
                    'communication_type_enum' => 'mail',
                    'is_seen' => $imapMessage->isSeen,
                ])
                    ->validate()
                    ->execute();
            }
        }

        return $this;
    }

    public function syncReadStatus(): static
    {
        $unreadUids = $this->messages
            ->reject(fn (ImapMessage $message) => $message->isSeen)
            ->map(fn (ImapMessage $message) => $message->uid)
            ->values()
            ->toArray();

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

    protected function fetchNewMessages(Folder $imapFolder): void
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
                $this->messages->push(ImapMessage::fromImapMessage($message));
            }
        } while ($page !== $messages->lastPage());
    }

    protected function fetchFilteredMessages(Folder $imapFolder): void
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
                $this->messages->push(ImapMessage::fromImapMessage($message));
            }
        } while ($page !== $messages->lastPage());
    }

    protected function createMessage(ImapMessage $imapMessage): void
    {
        $tagIds = $this->resolveTagIds($imapMessage->flags);

        CreateMailMessage::make([
            'mail_account_id' => $this->folder->mailAccount->getKey(),
            'mail_folder_id' => $this->folder->getKey(),
            'message_id' => $imapMessage->messageId,
            'message_uid' => $imapMessage->uid,
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
