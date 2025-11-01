<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use FluxErp\Livewire\Forms\WorkTimeTypeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class WorkTimeTypes extends WorkTimeTypeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public WorkTimeTypeForm $workTimeTypeForm;

    protected ?string $includeBefore = 'flux::livewire.settings.work-time-types';
}
