<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SepaMandate;

class SepaMandateList extends BaseDataTable
{
    protected string $model = SepaMandate::class;

    public array $enabledCols = [
        'contact_bank_connection.iban',
        'contact_bank_connection.bank_name',
        'signed_date',
    ];
}
