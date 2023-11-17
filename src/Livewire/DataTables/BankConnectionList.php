<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\BankConnection;
use TeamNiftyGmbH\DataTable\DataTable;

class BankConnectionList extends DataTable
{
    protected string $model = BankConnection::class;

    public array $enabledCols = [
        'is_active',
        'name',
        'iban',
        'account_holder',
        'bank_name',
        'bic',
    ];
}
