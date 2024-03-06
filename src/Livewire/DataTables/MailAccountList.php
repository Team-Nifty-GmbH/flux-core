<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailAccount;

class MailAccountList extends BaseDataTable
{
    protected string $model = MailAccount::class;

    public array $enabledCols = [
        'email',
    ];
}
