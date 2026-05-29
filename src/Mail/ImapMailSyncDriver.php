<?php

namespace FluxErp\Mail;

use FluxErp\Contracts\MailSyncDriver;
use FluxErp\Models\Communication;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;

class ImapMailSyncDriver implements MailSyncDriver
{
    public function syncFolders(MailAccount $account): array
    {
        return $account->syncFolders();
    }

    public function syncMessages(MailFolder $folder): void
    {
        $startUid = $this->resolveStartUid($folder);

        $builder = $folder->messages();

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

    protected function resolveStartUid(MailFolder $folder): ?int
    {
        $maxUid = resolve_static(Communication::class, 'query')
            ->where('mail_account_id', $folder->mailAccount->getKey())
            ->where('mail_folder_id', $folder->getKey())
            ->max('message_uid');

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
