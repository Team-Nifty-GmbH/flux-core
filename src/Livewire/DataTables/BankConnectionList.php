<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\BankConnection;

class BankConnectionList extends BaseDataTable
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
