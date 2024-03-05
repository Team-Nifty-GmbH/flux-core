<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailFolder\DeleteMailFolder;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webklex\PHPIMAP\Attachment;
use Webklex\PHPIMAP\Exceptions\ResponseException;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;

class SyncMailAccountJob implements Repeatable, ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $folderIds = [];

    private readonly MailAccount $mailAccount;

    public function __construct(MailAccount|string $email, public readonly bool $onlyFolders = false)
    {
        if (is_string($email)) {
            $this->mailAccount = MailAccount::query()
                ->where('email', $email)
                ->firstOrFail();
        } else {
            $this->mailAccount = $email;
        }
    }

    public function uniqueId(): string
    {
        return $this->mailAccount->uuid;
    }

    public function handle(): void
    {
        $client = $this->mailAccount->connect();

        $folders = $client->getFolders();
        foreach ($folders as $folder) {
            $this->folderIds = array_merge($this->folderIds, $this->createFolder($folder));
        }

        MailFolder::query()
            ->where('mail_account_id', $this->mailAccount->id)
            ->whereIntegerNotInRaw('id', array_values($this->folderIds))
            ->get('id')
            ->each(fn (MailFolder $folder) => DeleteMailFolder::make(['id' => $folder->id])->validate()->execute());

        if ($this->onlyFolders) {
            return;
        }

        foreach ($folders->reverse() as $folder) {
            if (! MailFolder::query()
                ->where('mail_account_id', $this->mailAccount->id)
                ->where('slug', $folder->path)
                ->first()
                ?->is_active
            ) {
                continue;
            }

            $this->getNewMessages($folder);
            $this->getUnseenMessages($folder);
        }
    }

    private function createFolder(Folder $folder, ?int $parentId = null): array
    {
        $folderIds = [];
        $mailFolder = MailFolder::query()
            ->where('mail_account_id', $this->mailAccount->id)
            ->where('slug', $folder->path)
            ->first();

        $action = $mailFolder?->id ? UpdateMailFolder::class : CreateMailFolder::class;

        $mailFolder = $action::make(
            [
                'id' => $mailFolder?->id,
                'mail_account_id' => $this->mailAccount->id,
                'parent_id' => $parentId,
                'name' => $folder->name,
                'slug' => $folder->path,
            ]
        )->validate()->execute();

        $folderIds[$folder->path] = $mailFolder->id;

        if ($folder->hasChildren()) {
            foreach ($folder->getChildren() as $child) {
                $folderIds = array_merge($folderIds, $this->createFolder($child, $mailFolder->id));
            }
        }

        return $folderIds;
    }

    private function getNewMessages(Folder $folder): void
    {
        $startUid = Communication::query()
            ->where('mail_account_id', $this->mailAccount->id)
            ->where('mail_folder_id', $this->folderIds[$folder->path])
            ->max('message_uid')
            ?? $folder->messages()->all()
                ->since($this->mailAccount->created_at)
                ->limit(1)
                ->get()
                ->first()
                ?->getUid() - 1
            ?: ($folder->examine()['uidnext'] ?? 0) - 1
                ?: 0;

        try {
            $query = $folder->messages()
                ->setFetchBody(false)
                ->leaveUnread()
                ->getByUidGreater($startUid);
        } catch (ResponseException) {
            return;
        }

        $page = 0;
        do {
            $page++;
            $messages = $query->paginate(100, $page);

            foreach ($messages as $message) {
                $this->storeMessage($message, $this->folderIds[$folder->path]);
            }
        } while ($page !== $messages->lastPage());
    }

    public function getUnseenMessages(Folder $folder): void
    {
        try {
            $query = $folder->messages()
                ->setFetchBody(false)
                ->leaveUnread()
                ->unseen()
                ->since($this->mailAccount->created_at);
        } catch (ResponseException) {
            return;
        }

        $unreadUids = [];

        $page = 0;
        do {
            $page++;
            $messages = $query->paginate(100, $page);
            $unreadUids[] = $messages->map(fn (Message $message) => $message->getUid())->toArray();
        } while ($page !== $messages->lastPage());

        Communication::query()
            ->where('mail_account_id', $this->mailAccount->id)
            ->where('mail_folder_id', $this->folderIds[$folder->path])
            ->where('is_seen', false)
            ->whereIntegerNotInRaw('message_uid', $unreadUids)
            ->each(
                fn (Communication $message) => UpdateCommunication::make(['id' => $message->id, 'is_seen' => true])
                    ->validate()
                    ->execute()
            );

        Communication::query()
            ->where('mail_account_id', $this->mailAccount->id)
            ->where('mail_folder_id', $this->folderIds[$folder->path])
            ->where('is_seen', true)
            ->whereIntegerInRaw('message_uid', $unreadUids)
            ->each(
                fn (Communication $message) => UpdateCommunication::make(['id' => $message->id, 'is_seen' => false])
                    ->validate()
                    ->execute()
            );
    }

    private function storeMessage(Message $message, int $folderId): void
    {
        $messageModel = Communication::query()
            ->where('mail_account_id', $this->mailAccount->id)
            ->where('message_id', $message->getMessageId())
            ->first();

        if (! $messageModel) {
            $message->parseBody();

            $attachments = [];
            foreach ($message->getAttachments() as $attachment) {
                /** @var Attachment $attachment */
                $attachments[] = [
                    'file_name' => $attachment->getName(),
                    'mime_type' => $attachment->getMimeType(),
                    'name' => $attachment->getName(),
                    'media_type' => 'string',
                    'media' => $attachment->getContent(),
                ];
            }

            $tags = $message->getFlags()->toArray();
            $tagIds = [];
            $type = app(Communication::class)->getMorphClass();
            $existingTags = app(Tag::class)->query()
                ->whereIn('name', $tags)
                ->where('type', $type)
                ->pluck('id', 'name')
                ->toArray();

            foreach ($tags as $tag) {
                if ($existingTag = data_get($existingTags, $tag)) {
                    $tagIds[] = $existingTag;
                } else {
                    $tagIds[] = CreateTag::make(['name' => $tag, 'type' => $type])->validate()->execute()->id;
                }
            }

            CreateMailMessage::make([
                'mail_account_id' => $this->mailAccount->id,
                'mail_folder_id' => $folderId,
                'message_id' => $message->getMessageId()->toString(),
                'message_uid' => $message->getUid(),
                'from' => $message->getFrom()[0]->full,
                'to' => $message->getTo()->toArray(),
                'cc' => $message->getCc()->toArray(),
                'bcc' => $message->getBcc()->toArray(),
                'communication_type_enum' => 'mail',
                'date' => $message->getDate()->toDate(),
                'subject' => $message->getSubject()->toString(),
                'text_body' => $message->getTextBody(),
                'html_body' => $message->getHtmlBody(),
                'is_seen' => $message->hasFlag('seen'),
                'tags' => $tagIds,
                'attachments' => $attachments,
            ])
                ->validate()
                ->execute();
        } else {
            UpdateCommunication::make([
                'id' => $messageModel->id,
                'mail_folder_id' => $folderId,
                'message_uid' => $message->getUid(),
                'communication_type_enum' => 'mail',
                'is_seen' => $message->hasFlag('seen'),
            ])
                ->validate()
                ->execute();
        }
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(self::class);
    }

    public static function description(): ?string
    {
        return 'Import Mails from given Mail Account.';
    }

    public static function parameters(): array
    {
        return [
            'email' => null,
        ];
    }

    public static function defaultCron(): ?CronExpression
    {
        return null;
    }
}
