<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

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
