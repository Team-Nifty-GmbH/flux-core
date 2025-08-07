<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\AbsenceTypeList;
use FluxErp\Livewire\Forms\AbsenceTypeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class AbsenceTypes extends AbsenceTypeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public AbsenceTypeForm $absenceTypeForm;

    protected ?string $includeBefore = 'flux::livewire.settings.absence-types';
}