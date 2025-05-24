<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\DataTables\LeadList as BaseLeadList;
use FluxErp\Livewire\Forms\LeadForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;

class LeadList extends BaseLeadList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::edit as dataTableEdit;
        DataTableHasFormEdit::save as dataTableSave;
    }

    public ?string $includeBefore = 'flux::livewire.lead.lead-list';

    #[DataTableForm]
    public LeadForm $leadForm;

    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        if ($id) {
            $this->redirectRoute(name: 'sales.lead.id', parameters: [$id], navigate: true);

            return;
        }

        $this->dataTableEdit($id);
    }

    #[Renderless]
    public function evaluate(): void {}

    #[Renderless]
    public function save(): bool
    {
        $result = $this->dataTableSave();

        if ($result) {
            $this->edit($this->leadForm->id);
        }

        return $result;
    }
}
