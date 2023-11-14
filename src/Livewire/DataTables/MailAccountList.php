<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\MailAccount;
use TeamNiftyGmbH\DataTable\DataTable;

class MailAccountList extends DataTable
{
    protected string $model = MailAccount::class;

    public array $enabledCols = [
        'email',
    ];
}
