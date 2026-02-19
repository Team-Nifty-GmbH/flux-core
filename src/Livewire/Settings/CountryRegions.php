<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\CountryRegionList;
use FluxErp\Livewire\Forms\CountryRegionForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;

class CountryRegions extends CountryRegionList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public CountryRegionForm $countryRegionForm;

    protected ?string $includeBefore = 'flux::livewire.settings.country-regions';
}
