<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ContactBankConnection;

class ContactBankConnectionList extends BaseDataTable
{
    protected string $model = ContactBankConnection::class;

    public array $enabledCols = [
        'iban',
        'bank_name',
    ];
}
