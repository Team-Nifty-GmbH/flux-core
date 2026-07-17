<?php

namespace FluxErp\Contracts;

use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;

interface MailSyncDriver
{
    /**
     * Sync the folder tree for the given mail account.
     *
     * @return array<string, int> Map of remote folder identifier to local MailFolder primary key.
     */
    public function syncFolders(MailAccount $account): array;

    /**
     * Sync messages for the given folder, persisting new/updated communications
     * and reconciling read/unread status.
     */
    public function syncMessages(MailFolder $folder): void;

    /**
     * Verify the account credentials and reachability of the remote service.
     */
    public function testConnection(MailAccount $account): bool;
}
