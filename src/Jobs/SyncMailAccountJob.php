<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMailAccountJob implements Repeatable, ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private readonly MailAccount $mailAccount;

    public function __construct(MailAccount|string $email, public readonly bool $onlyFolders = false)
    {
        if (is_string($email)) {
            $this->mailAccount = resolve_static(MailAccount::class, 'query')
                ->where('email', $email)
                ->firstOrFail();
        } else {
            $this->mailAccount = $email;
        }
    }

    public static function defaultCron(): ?CronExpression
    {
        return null;
    }

    public static function description(): ?string
    {
        return 'Import Mails from given Mail Account.';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(self::class);
    }

    public static function parameters(): array
    {
        return [
            'email' => null,
        ];
    }

    public function handle(): void
    {
        $this->mailAccount->syncFolders();

        if ($this->onlyFolders) {
            return;
        }

        $this->mailAccount->mailFolders()
            ->where('is_active', true)
            ->each(function (MailFolder $folder): void {
                $startUid = $this->resolveStartUid($folder);

                $folder->messages()->newSince($startUid)->fetch()->store();
                $folder->messages()->unseen()->fetch()->syncReadStatus();
            });
    }

    public function uniqueId(): string
    {
        return $this->mailAccount->uuid;
    }

    private function resolveStartUid(MailFolder $folder): int
    {
        $maxUid = resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $this->mailAccount->getKey())
            ->where('mail_folder_id', $folder->getKey())
            ->max('message_uid');

        if ($maxUid) {
            return (int) $maxUid;
        }

        // No messages stored yet - determine starting UID from IMAP
        $client = $this->mailAccount->getImapClient();

        if (! $client) {
            return 0;
        }

        $imapFolder = $client->getFolders(false, $folder->slug)->first();

        if (! $imapFolder) {
            return 0;
        }

        $firstMessage = $imapFolder->messages()
            ->all()
            ->since($this->mailAccount->created_at)
            ->limit(1)
            ->get()
            ?->first();

        if ($firstMessage) {
            return max($firstMessage->getUid() - 1, 0);
        }

        return max(($imapFolder->examine()['uidnext'] ?? 0) - 1, 0);
    }
}
