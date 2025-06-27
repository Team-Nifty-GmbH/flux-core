<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\LeadLossReasonList;
use FluxErp\Livewire\Forms\LeadLossReasonForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class LeadLossReasons extends LeadLossReasonList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public LeadLossReasonForm $leadLossReasonForm;

    protected ?string $includeBefore = 'flux::livewire.settings.lead-loss-reasons';
}
