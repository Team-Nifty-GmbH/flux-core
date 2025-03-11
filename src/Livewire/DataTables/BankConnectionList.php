<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\BankConnection;

class BankConnectionList extends BaseDataTable
{
    public array $enabledCols = [
        'is_active',
        'name',
        'iban',
        'account_holder',
        'bank_name',
        'bic',
    ];

    protected string $model = BankConnection::class;
}
