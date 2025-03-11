<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\LedgerAccount;

class LedgerAccountList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'number',
        'ledger_account_type_enum',
        'is_automatic',
    ];

    protected string $model = LedgerAccount::class;
}
