<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\VacationCarryoverRuleList;
use FluxErp\Livewire\Forms\VacationCarryoverRuleForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class VacationCarryoverRules extends VacationCarryoverRuleList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public VacationCarryoverRuleForm $vacationCarryoverRuleForm;

    protected ?string $includeBefore = 'flux::livewire.settings.vacation-carryover-rules';
}