<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\LeadStateList;
use FluxErp\Livewire\Forms\LeadStateForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class LeadStates extends LeadStateList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public LeadStateForm $leadStateForm;

    protected ?string $includeBefore = 'flux::livewire.settings.lead-states';
}
