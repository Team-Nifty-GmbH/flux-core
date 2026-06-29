<?php

namespace FluxErp\Mail;

use Closure;
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
        $startUid = $this->resolveStartUid($folder);

        $builder = $folder->messages()->onProgress($this->progressCallback);

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

    protected function resolveStartUid(MailFolder $folder): ?int
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

        if ($maxUid) {
            return (int) $maxUid;
        }

        $client = $folder->mailAccount->getImapClient();

        if (! $client) {
            return null;
        }

        $imapFolder = $client->getFolderByPath($folder->slug, utf7: true);

        if (! $imapFolder) {
            return null;
        }

        $firstMessage = $imapFolder->messages()
            ->all()
            ->since($folder->mailAccount->created_at)
            ->limit(1)
            ->get()
            ?->first();

        if ($firstMessage) {
            return max($firstMessage->getUid() - 1, 0);
        }

        return ! is_null($uidnext = data_get($imapFolder->examine(), 'uidnext'))
            ? max($uidnext - 1, 0)
            : null;
    }
}
