<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SepaMandate;

class SepaMandateList extends BaseDataTable
{
    public array $enabledCols = [
        'contact_bank_connection.iban',
        'contact_bank_connection.bank_name',
        'signed_date',
        'sepa_mandate_type_enum',
    ];

    protected string $model = SepaMandate::class;
}
