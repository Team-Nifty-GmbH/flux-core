<?php

namespace FluxErp\Jobs;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Mail\MailDriverManager;
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
        $driver = app(MailDriverManager::class)->driver($this->mailAccount->protocol);

        $driver->syncFolders($this->mailAccount);

        if ($this->onlyFolders) {
            return;
        }

        $this->mailAccount->mailFolders()
            ->where('is_active', true)
            ->each(fn (MailFolder $folder) => $driver->syncMessages($folder));
    }

    public function uniqueId(): string
    {
        return $this->mailAccount->uuid;
    }
}
