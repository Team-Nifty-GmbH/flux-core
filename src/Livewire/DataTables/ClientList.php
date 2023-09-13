<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Client;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class ClientList extends DataTable
{
    protected string $model = Client::class;

    public array $enabledCols = [
        'name',
        'client_code',
        'country.name',
        'zip',
        'city',
        'phone',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
