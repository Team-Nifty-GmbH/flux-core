<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailAccount;

class MailAccountList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
    ];

    protected string $model = MailAccount::class;
}
