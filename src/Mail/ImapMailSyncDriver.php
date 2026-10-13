<?php

namespace FluxErp\Mail;

use Closure;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Contracts\MailSyncDriver;
use FluxErp\Contracts\ReportsSyncProgress;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use Webklex\PHPIMAP\Folder;

class ImapMailSyncDriver implements MailSyncDriver, ReportsSyncProgress
{
    protected ?Closure $progressCallback = null;

    public function syncFolders(MailAccount $account): array
    {
        return $account->syncFolders();
    }

    public function syncMessages(MailFolder $folder): void
    {
        $imapFolder = $folder->mailAccount
            ->getImapClient()
            ?->getFolderByPath($folder->slug, utf7: true);
        $status = $imapFolder?->examine() ?? [];
        $uidValidity = data_get($status, 'uidvalidity');

        // A new UIDVALIDITY means the server numbered the mailbox anew, so the
        // stored uids point at other messages or at none.
        $uidsAreStale = ! is_null($uidValidity)
            && ! is_null($folder->uid_validity)
            && $folder->uid_validity !== (int) $uidValidity;

        $startUid = $this->resolveStartUid($folder, $imapFolder, $status, $uidsAreStale);

        if ($uidsAreStale) {
            resolve_static(Communication::class, 'query')
                ->where('mail_account_id', $folder->mailAccount->getKey())
                ->where('mail_folder_id', $folder->getKey())
                ->update(['message_uid' => null]);
        }

        $builder = $folder->messages()->onProgress($this->progressCallback);

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

    protected function resolveStartUid(
        MailFolder $folder,
        ?Folder $imapFolder,
        array $status,
        bool $uidsAreStale
    ): ?int {
        if (! $uidsAreStale) {
            // message_uid is a string column to accommodate non-numeric provider ids
            // (Graph, Gmail history ids). For IMAP-family drivers the value is always
            // numeric, but a lexicographic MAX would return "9" for {"9","10"}.
            // Cast to unsigned so the comparison stays numeric.
            $maxUid = resolve_static(Communication::class, 'query')
                ->where('mail_account_id', $folder->mailAccount->getKey())
                ->where('mail_folder_id', $folder->getKey())
                ->selectRaw('MAX(CAST(message_uid AS UNSIGNED)) AS max_uid')
                ->value('max_uid');

            if ($maxUid) {
                return (int) $maxUid;
            }
        }

        if (! $imapFolder) {
            return null;
        }

        // Stale uids restart at the newest stored mail, storeMessage() matches the
        // messages from there on by their message id and rewrites their uid.
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
