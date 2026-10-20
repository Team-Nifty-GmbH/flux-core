<?php

namespace FluxErp\Console\Commands;

use FluxErp\Jobs\SyncMailAccountJob;
use FluxErp\Models\MailAccount;
use Illuminate\Bus\UniqueLock;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository;
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

    protected function dispatchJob(SyncMailAccountJob $job): bool
    {
        if (! (new UniqueLock(app(Repository::class)))->acquire($job)) {
            return false;
        }

        app(Dispatcher::class)->dispatch($job);

        return true;
    }

    protected function listen(int $accountId, mixed $restartSignal): int
    {
        $mailAccount = $this->listenableAccounts()
            ->whereKey($accountId)
            ->first();
        $inbox = $mailAccount?->mailFolders()
            ->where('slug', 'INBOX')
            ->where('is_active', true)
            ->first();

        if (is_null($inbox)) {
            return static::FAILURE;
        }

        try {
            $client = $mailAccount->getImapClient();

            if (is_null($client)) {
                return static::FAILURE;
            }

            $connection = $client->getConnection();

            if (! in_array('IDLE', $connection->getCapabilities()->validatedData())) {
                return static::INVALID;
            }

            $client->openFolder($inbox->slug, true);
            $syncPending = false;

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

                        $line = '';
                    }

                    if ($syncPending || stripos($line, 'EXISTS') !== false) {
                        $syncPending = ! $this->dispatchJob(
                            app(SyncMailAccountJob::class, [
                                'email' => $mailAccount,
                                'onlyFolders' => false,
                                'mailFolderId' => $inbox->getKey(),
                            ])
                        );
                    }
                }

                $connection->done();
            }
        } catch (ConnectionFailedException|RuntimeException $exception) {
            report($exception);

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
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('protocol')
                ->orWhere('protocol', 'imap')
            )
            ->where('failed_logins', '<', MailAccount::MAX_FAILED_LOGINS)
            ->whereHas('mailFolders', fn (Builder $query): Builder => $query
                ->where('slug', 'INBOX')
                ->where('is_active', true)
            );
    }

    protected function shouldRestart(mixed $restartSignal): bool
    {
        return Cache::get('illuminate:queue:restart') !== $restartSignal;
    }

    protected function supervise(mixed $restartSignal): int
    {
        /** @var array<int, InvokedProcess> $processes */
        $processes = [];
        $versions = [];
        $withoutIdle = [];

        while (! $this->shouldRestart($restartSignal)) {
            $accounts = $this->listenableAccounts()
                ->pluck('updated_at', 'id')
                ->map(fn (mixed $updatedAt): string => (string) $updatedAt);

            foreach ($processes as $accountId => $process) {
                if ($accounts->get($accountId) !== $versions[$accountId]) {
                    $process->stop();
                    unset($processes[$accountId], $versions[$accountId]);
                } elseif (! $process->running() && $process->wait()->exitCode() === static::INVALID) {
                    $withoutIdle[$accountId] = $versions[$accountId];
                    unset($processes[$accountId], $versions[$accountId]);
                }
            }

            foreach ($accounts as $accountId => $version) {
                if (($withoutIdle[$accountId] ?? null) === $version
                    || ($processes[$accountId] ?? null)?->running()
                ) {
                    continue;
                }

                $processes[$accountId] = Process::path(base_path())
                    ->forever()
                    ->start([PHP_BINARY, 'artisan', $this->getName(), '--account=' . $accountId]);
                $versions[$accountId] = $version;
            }

            Sleep::for(60)->seconds();
        }

        foreach ($processes as $process) {
            $process->stop();
        }

        return static::SUCCESS;
    }
}
