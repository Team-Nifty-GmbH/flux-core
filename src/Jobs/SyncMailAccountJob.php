<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Contracts\ReportsSyncProgress;
use FluxErp\Contracts\ShouldBeMonitored;
use FluxErp\Mail\MailDriverManager;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Traits\IsMonitored;
use FluxErp\Traits\Job\TracksSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMailAccountJob implements Repeatable, ShouldBeMonitored, ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels, TracksSchedule;

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

    public function getName(): string
    {
        return __('Mail sync :email', ['email' => $this->mailAccount->email]);
    }

    public function handle(): void
    {
        $driver = app(MailDriverManager::class)->driver($this->mailAccount->protocol);

        $driver->syncFolders($this->mailAccount);

        if ($this->onlyFolders) {
            return;
        }

        $folders = $this->mailAccount->mailFolders()
            ->where('is_active', true)
            ->get();
        $total = $folders->count();

        try {
            $folders->each(function (MailFolder $folder, int $index) use ($driver, $total): void {
                if ($driver instanceof ReportsSyncProgress) {
                    $driver->withProgressCallback(
                        fn (int $processed, int $totalMessages) => $this->queueUpdate([
                            'progress' => ($index + ($totalMessages > 0 ? min($processed / $totalMessages, 1) : 1))
                                / $total
                                * 100,
                            'message' => __(':processed of :total mails (:folder)', [
                                'processed' => $processed,
                                'total' => $totalMessages,
                                'folder' => $folder->name,
                            ]),
                        ])
                    );
                }

                $driver->syncMessages($folder);
                $this->queueProgressChunk($total, 1);
            });
        } finally {
            if ($driver instanceof ReportsSyncProgress) {
                $driver->withProgressCallback(null);
            }
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
}
