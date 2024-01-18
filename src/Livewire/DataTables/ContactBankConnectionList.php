<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ContactBankConnection;
use TeamNiftyGmbH\DataTable\DataTable;

class ContactBankConnectionList extends DataTable
{
    protected string $model = ContactBankConnection::class;

    public array $enabledCols = [
        'iban',
        'bank_name',
    ];
}
