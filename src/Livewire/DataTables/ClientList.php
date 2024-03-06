<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Client;

class ClientList extends BaseDataTable
{
    protected string $model = Client::class;

    public array $enabledCols = [
        'name',
        'client_code',
        'country.name',
        'postcode',
        'city',
        'phone',
    ];
}
