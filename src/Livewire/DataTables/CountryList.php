<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Country;
use Illuminate\Database\Eloquent\Builder;

class CountryList extends BaseDataTable
{
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

    protected string $model = Country::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with([
            'language:id,name',
            'currency:id,name',
        ]);
    }
}
