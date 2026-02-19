<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Traits\Job\TracksSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMailAccountJob implements Repeatable, ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TracksSchedule;

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

    public static function withoutOverlapping(): bool
    {
        return true;
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

                $builder = $folder->messages();

                if (! is_null($startUid)) {
                    $builder->newSince($startUid);
                }

                $builder->fetchAndStore();

                $builder
                    ->reset()
                    ->unseen()
                    ->withoutBody()
                    ->fetch()
                    ->syncReadStatus();
            });
    }

    public function uniqueId(): string
    {
        return $this->mailAccount->uuid;
    }

    protected function resolveStartUid(MailFolder $folder): ?int
    {
        $maxUid = resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $this->mailAccount->getKey())
            ->where('mail_folder_id', $folder->getKey())
            ->max('message_uid');

        if ($maxUid) {
            return (int) $maxUid;
        }

        $client = $this->mailAccount->getImapClient();

        if (! $client) {
            return null;
        }

        $imapFolder = $client->getFolderByPath($folder->slug, utf7: true);

        if (! $imapFolder) {
            return null;
        }

        $firstMessage = $imapFolder->messages()
            ->all()
            ->since($this->mailAccount->created_at)
            ->limit(1)
            ->get()
            ?->first();

        if ($firstMessage) {
            return max($firstMessage->getUid() - 1, 0) ?: null;
        }

        return ! is_null($uidnext = data_get($imapFolder->examine(), 'uidnext'))
            ? max($uidnext - 1, 0)
            : null;
    }
}
