<?php

namespace FluxErp\Mail;

use Closure;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Contracts\MailSyncDriver;
use FluxErp\Contracts\ReportsSyncProgress;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;

class ImapMailSyncDriver implements MailSyncDriver, ReportsSyncProgress
{
    protected ?Closure $progressCallback = null;

    public function syncFolders(MailAccount $account): array
    {
        return $account->syncFolders();
    }

    public function syncMessages(MailFolder $folder): void
    {
        $builder = $folder->messages()->onProgress($this->progressCallback);

        $status = $builder->status();
        $uidValidity = data_get($status, 'uidvalidity');
        $uidsAreStale = ! is_null($uidValidity)
            && ! is_null($folder->uid_validity)
            && $folder->uid_validity !== (int) $uidValidity;

        $startUid = $this->resolveStartUid($folder, $status, $uidsAreStale);

        if ($uidsAreStale) {
            resolve_static(Communication::class, 'query')
                ->where('mail_account_id', $folder->mailAccount->getKey())
                ->where('mail_folder_id', $folder->getKey())
                ->update(['message_uid' => null]);

            resolve_static(MailFolder::class, 'query')
                ->whereKey($folder->getKey())
                ->update(['last_skipped_uid' => null]);
        }

        if (! is_null($startUid)) {
            $builder->newSince($startUid);
        }

        $builder->fetchAndStore();

        $builder->syncReadStatus();

        if (! is_null($uidValidity) && $folder->uid_validity !== (int) $uidValidity) {
            UpdateMailFolder::make([
                'id' => $folder->getKey(),
                'uid_validity' => (int) $uidValidity,
            ])
                ->validate()
                ->execute();
        }
    }

    public function testConnection(MailAccount $account): bool
    {
        return ! is_null($account->connect());
    }

    public function withProgressCallback(?Closure $callback): static
    {
        $this->progressCallback = $callback;

        return $this;
    }

    protected function resolveStartUid(MailFolder $folder, array $status, bool $uidsAreStale): ?int
    {
        // message_uid is a string column to accommodate non-numeric provider ids
        // (Graph, Gmail history ids). For IMAP-family drivers the value is always
        // numeric, but a lexicographic MAX would return "9" for {"9","10"}.
        // Cast to unsigned so the comparison stays numeric.
        $maxUid = resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $folder->mailAccount->getKey())
            ->where('mail_folder_id', $folder->getKey())
            ->selectRaw('MAX(CAST(message_uid AS UNSIGNED)) AS max_uid')
            ->value('max_uid');

        $maxUid = max((int) $maxUid, (int) $folder->last_skipped_uid);

        if ($maxUid && ! $uidsAreStale) {
            return $maxUid;
        }

        $imapFolder = $folder->mailAccount->getImapFolder($folder->slug);

        if (! $imapFolder) {
            return null;
        }

        $since = $uidsAreStale
            ? resolve_static(Communication::class, 'query')
                ->where('mail_account_id', $folder->mailAccount->getKey())
                ->where('mail_folder_id', $folder->getKey())
                ->max('date')
            : null;

        $firstMessage = $imapFolder->messages()
            ->all()
            ->since($since ?? $folder->mailAccount->created_at)
            ->limit(1)
            ->get()
            ?->first();

        if ($firstMessage) {
            return max($firstMessage->getUid() - 1, 0);
        }

        return ! is_null($uidnext = data_get($status, 'uidnext'))
            ? max($uidnext - 1, 0)
            : null;
    }
}
