<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\LocationList;
use FluxErp\Livewire\Forms\LocationForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class Locations extends LocationList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public LocationForm $locationForm;

    protected ?string $includeBefore = 'flux::livewire.settings.locations';
}