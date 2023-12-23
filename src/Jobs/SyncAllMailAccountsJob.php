<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Models\MailAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SyncAllMailAccountsJob implements Repeatable, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mailAccounts = MailAccount::all();

        foreach ($mailAccounts as $mailAccount) {
            SyncMailAccountJob::dispatch($mailAccount);
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
        return 'Import Mails from all Mail Accounts.';
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('*/15 * * * *');
    }
}
