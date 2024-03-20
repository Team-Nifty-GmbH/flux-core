<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\LedgerAccount;

class LedgerAccountList extends BaseDataTable
{
    protected string $model = LedgerAccount::class;

    public array $enabledCols = [
        'name',
        'number',
        'ledger_account_type_enum',
        'is_automatic',
    ];
}
