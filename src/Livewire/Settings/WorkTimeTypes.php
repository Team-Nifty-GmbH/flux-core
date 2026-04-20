<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use FluxErp\Livewire\Forms\WorkTimeTypeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Livewire\DataTable\DataTableHasInlineEdit;

class WorkTimeTypes extends WorkTimeTypeList
{
    use DataTableHasFormEdit;
    use DataTableHasInlineEdit;

    #[DataTableForm]
    public WorkTimeTypeForm $workTimeTypeForm;

    protected ?string $includeBefore = 'flux::livewire.settings.work-time-types';
}
