<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class CountryList extends DataTable
{
    protected string $model = Country::class;

    public array $enabledCols = [
        'name',
        'language.name',
        'currency.name',
        'iso_alpha2',
        'iso_alpha3',
        'iso_numeric',
        'is_active',
        'is_default',
        'is_eu_country',
    ];

    public array $columnLabels = [
        'language.name' => 'Language',
        'currency.name' => 'Currency',
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
            'language:id,name',
            'currency:id,name',
        ]);
    }
}
