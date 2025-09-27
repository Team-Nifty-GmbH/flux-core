<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\VacationCarryOverRuleList;
use FluxErp\Livewire\Forms\VacationCarryOverRuleForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class VacationCarryoverRules extends VacationCarryOverRuleList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public VacationCarryOverRuleForm $vacationCarryoverRuleForm;

    protected ?string $includeBefore = 'flux::livewire.settings.vacation-carryover-rules';
}
