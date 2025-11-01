<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\HolidayList;
use FluxErp\Livewire\Forms\HolidayForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class Holidays extends HolidayList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public HolidayForm $holidayForm;

    protected ?string $includeBefore = 'flux::livewire.settings.holidays';
}
