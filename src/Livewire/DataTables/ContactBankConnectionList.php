<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ContactBankConnection;

class ContactBankConnectionList extends BaseDataTable
{
    public array $enabledCols = [
        'iban',
        'bank_name',
    ];

    protected string $model = ContactBankConnection::class;
}
