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

    public array $columnLabels = [
        'country.name' => 'Country',
        'postcode' => 'Zip',
    ];

    public function mount(): void
    {
        $attributes = ModelInfo::forModel($this->model)->attributes;

        $this->availableCols = $attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'country:id,name',
        ]);
    }
}
