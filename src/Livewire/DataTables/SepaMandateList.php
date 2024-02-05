<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SepaMandate;
use TeamNiftyGmbH\DataTable\DataTable;

class SepaMandateList extends DataTable
{
    protected string $model = SepaMandate::class;

    public array $enabledCols = [
        'contact_bank_connection.iban',
        'contact_bank_connection.bank_name',
        'signed_date',
    ];
}
