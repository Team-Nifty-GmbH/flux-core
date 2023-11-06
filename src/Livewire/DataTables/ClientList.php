<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Client;
use TeamNiftyGmbH\DataTable\DataTable;

class ClientList extends DataTable
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

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];
}
