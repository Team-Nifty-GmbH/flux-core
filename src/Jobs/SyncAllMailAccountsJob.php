<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Mail\MailDriverManager;
use FluxErp\Models\MailAccount;
use FluxErp\Traits\Job\TracksSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SyncAllMailAccountsJob implements Repeatable, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, TracksSchedule;

    public function __construct()
    {
        //
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('*/15 * * * *');
    }

    public static function description(): ?string
    {
        return 'Import Mails from all Mail Accounts.';
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
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return true;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        resolve_static(MailAccount::class, 'query')
            ->where(fn (Builder $query): Builder => $query
                ->whereNotNull('host')
                ->orWhereNotIn('protocol', app(MailDriverManager::class)->imapFamilyDriverNames())
            )
            ->each(fn (MailAccount $mailAccount) => SyncMailAccountJob::dispatch($mailAccount));
    }
}
