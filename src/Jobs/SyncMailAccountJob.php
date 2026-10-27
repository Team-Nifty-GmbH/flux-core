<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Contracts\ReportsSyncProgress;
use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Exceptions\MailSyncTimeBudgetExhausted;
use FluxErp\Mail\ImapMessageBuilder;
use FluxErp\Mail\MailDriverManager;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Traits\IsMonitored;
use FluxErp\Traits\Job\TracksSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;
use Webklex\PHPIMAP\Exceptions\ImapServerErrorException;

class SyncMailAccountJob implements Repeatable, ShouldBeMonitored, ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels, TracksSchedule;

    public const int CONNECTION_FAILURE_GRACE_MINUTES = 30;

    private readonly MailAccount $mailAccount;

    public function __construct(
        MailAccount|string $email,
        public readonly bool $onlyFolders = false,
        public readonly ?int $mailFolderId = null
    ) {
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

    public function getName(): string
    {
        return __('Mail sync :email', ['email' => $this->mailAccount->email]);
    }

    public function handle(): void
    {
        if ($this->mailAccount->isSyncPaused()) {
            return;
        }

        try {
            $this->sync();

            Cache::forget($this->connectionFailureCacheKey());
        } catch (Throwable $e) {
            if (! $this->isConnectionFailure($e) || $this->isOutageOverdue()) {
                throw $e;
            }

            Log::warning('Mail server unreachable, the next sync run tries again: ' . $e->getMessage(), [
                'mail_account_id' => $this->mailAccount->getKey(),
            ]);
        } finally {
            $this->mailAccount->disconnectImapClient();
        }
    }

    public function progressCooldown(): int
    {
        return 2;
    }

    public function uniqueId(): string
    {
        return $this->mailAccount->uuid;
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    protected function connectionFailureCacheKey(): string
    {
        return 'mail-sync-connection-failure:' . $this->mailAccount->uuid;
    }

    protected function isConnectionFailure(Throwable $e): bool
    {
        return resolve_static(ImapMessageBuilder::class, 'isLostConnection', [$e])
            || ($e instanceof ImapServerErrorException
                && preg_match('/\[(AUTHENTICATIONFAILED|UNAVAILABLE)\]/', $e->getMessage()));
    }

    protected function isOutageOverdue(): bool
    {
        Cache::add($this->connectionFailureCacheKey(), now()->getTimestamp());

        return now()->getTimestamp() - Cache::get($this->connectionFailureCacheKey())
            >= static::CONNECTION_FAILURE_GRACE_MINUTES * 60;
    }

    protected function sync(): void
    {
        $driver = app(MailDriverManager::class)->driver($this->mailAccount->protocol);

        if (is_null($this->mailFolderId)) {
            $driver->syncFolders($this->mailAccount);
        }

        if ($this->onlyFolders) {
            return;
        }

        $folders = $this->mailAccount->mailFolders()
            ->where('is_active', true)
            ->when($this->mailFolderId, fn (Builder $query, int $id): Builder => $query->whereKey($id))
            ->get()
            ->each(fn (MailFolder $folder) => $folder->setRelation('mailAccount', $this->mailAccount));
        $total = $folders->count();
        $failure = null;
        $deadline = now()->addSeconds($this->uniqueFor() - 600);

        try {
            $folders->each(function (MailFolder $folder, int $index) use (
                $driver,
                $total,
                $deadline,
                &$failure
            ): bool {
                if (now()->greaterThanOrEqualTo($deadline)) {
                    return false;
                }

                if ($driver instanceof ReportsSyncProgress) {
                    $driver->withProgressCallback(
                        function (int $processed, int $totalMessages) use (
                            $index,
                            $total,
                            $folder,
                            $deadline
                        ): void {
                            $folderProgress = $totalMessages > 0
                                ? min($processed / $totalMessages, 1)
                                : 1;

                            $this->queueUpdate([
                                'progress' => ($index + $folderProgress) / $total * 100,
                                'message' => __(':processed of :total mails (:folder)', [
                                    'processed' => $processed,
                                    'total' => $totalMessages,
                                    'folder' => $folder->name,
                                ]),
                            ]);

                            if (now()->greaterThanOrEqualTo($deadline)) {
                                throw new MailSyncTimeBudgetExhausted();
                            }
                        }
                    );
                }

                // One broken folder must not keep the others from syncing.
                // The first failure fails the job once all folders had their turn.
                try {
                    $driver->syncMessages($folder);
                } catch (MailSyncTimeBudgetExhausted) {
                    return false;
                } catch (Throwable $e) {
                    if ($this->isConnectionFailure($e)) {
                        if (! is_null($failure)) {
                            report($failure);
                        }

                        throw $e;
                    }

                    if (is_null($failure)) {
                        $failure = $e;
                    } else {
                        report($e);
                    }
                }

                $this->queueProgressChunk($total, 1);

                return true;
            });

            if (! is_null($failure)) {
                throw $failure;
            }
        } finally {
            if ($driver instanceof ReportsSyncProgress) {
                $driver->withProgressCallback(null);
            }
        }
    }
}
