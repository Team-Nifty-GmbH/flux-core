<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\AbsencePolicyList;
use FluxErp\Livewire\Forms\AbsencePolicyForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;

class AbsencePolicies extends AbsencePolicyList
{
    use DataTableHasFormEdit;

    public bool $rendersForm = false;

    #[DataTableForm]
    public AbsencePolicyForm $absencePolicyForm;

    protected ?string $includeBefore = 'flux::livewire.settings.absence-policies';
}
