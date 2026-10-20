<?php

namespace FluxErp\Console\Commands;

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use Illuminate\Console\Command;
use Illuminate\Contracts\Process\InvokedProcess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Webklex\PHPIMAP\Connection\Protocols\Response;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\RuntimeException;

class MailListenCommand extends Command
{
    protected $description = 'Listen for new mails via IMAP IDLE and sync the inbox as soon as one arrives.';

    protected $signature = 'flux:mail-listen {--account= : Listen on this mail account only}';

    public function handle(): int
    {
        $restartSignal = Cache::get('illuminate:queue:restart');

        return $this->option('account')
            ? $this->listen((int) $this->option('account'), $restartSignal)
            : $this->supervise($restartSignal);
    }

    protected function supervise(mixed $restartSignal): int
    {
        /** @var array<int, InvokedProcess> $processes */
        $processes = [];

        while (! $this->shouldRestart($restartSignal)) {
            $accountIds = $this->listenableAccounts()->pluck('id')->all();

            foreach (array_diff(array_keys($processes), $accountIds) as $accountId) {
                $processes[$accountId]->signal(SIGTERM);
                unset($processes[$accountId]);
            }

            foreach ($accountIds as $accountId) {
                if (! ($processes[$accountId] ?? null)?->running()) {
                    $processes[$accountId] = Process::path(base_path())
                        ->forever()
                        ->start([PHP_BINARY, 'artisan', $this->getName(), '--account=' . $accountId]);
                }
            }

            Sleep::for(60)->seconds();
        }

        foreach ($processes as $process) {
            $process->signal(SIGTERM);
        }

        return static::SUCCESS;
    }

    protected function listen(int $accountId, mixed $restartSignal): int
    {
        $mailAccount = $this->listenableAccounts()->whereKey($accountId)->first();
        $inbox = $mailAccount?->mailFolders()
            ->where('slug', 'INBOX')
            ->where('is_active', true)
            ->first();

        if (! $inbox) {
            return static::FAILURE;
        }

        try {
            $client = $mailAccount->getImapClient();

            if (! $client) {
                return static::FAILURE;
            }

            $client->openFolder($inbox->slug, true);
            $connection = $client->getConnection();

            while (! $this->shouldRestart($restartSignal)) {
                $connection->idle();
                $renewAt = now()->addMinutes(25);

                while (now()->lessThan($renewAt) && ! $this->shouldRestart($restartSignal)) {
                    try {
                        $line = $connection->nextLine(Response::empty());
                    } catch (RuntimeException $exception) {
                        if (! str_contains($exception->getMessage(), 'empty response')) {
                            throw $exception;
                        }

                        continue;
                    }

                    if (str_contains($line, 'EXISTS')) {
                        SyncMailAccountJob::dispatch($mailAccount, false, $inbox->getKey());
                    }
                }

                $connection->done();
            }
        } catch (ConnectionFailedException|RuntimeException) {
            return static::FAILURE;
        } finally {
            $mailAccount->disconnectImapClient();
        }

        return static::SUCCESS;
    }

    protected function listenableAccounts(): Builder
    {
        return resolve_static(MailAccount::class, 'query')
            ->whereNotNull('host')
            ->where(fn (Builder $query): Builder => $query->whereNull('protocol')->orWhere('protocol', 'imap'))
            ->where('failed_logins', '<', MailAccount::MAX_FAILED_LOGINS)
            ->whereHas(
                'mailFolders',
                fn (Builder $query): Builder => $query->where('slug', 'INBOX')->where('is_active', true)
            );
    }

    protected function shouldRestart(mixed $restartSignal): bool
    {
        return Cache::get('illuminate:queue:restart') !== $restartSignal;
    }
}
