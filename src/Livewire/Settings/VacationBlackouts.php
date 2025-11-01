<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\VacationBlackoutList;
use FluxErp\Livewire\Forms\VacationBlackoutForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class VacationBlackouts extends VacationBlackoutList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public VacationBlackoutForm $vacationBlackoutForm;

    protected ?string $includeBefore = 'flux::livewire.settings.vacation-blackouts';
}
