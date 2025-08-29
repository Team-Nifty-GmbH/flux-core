<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\DataTables\AbsenceRequestList;
use FluxErp\Livewire\Forms\AbsenceRequestForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class AbsenceRequests extends AbsenceRequestList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public AbsenceRequestForm $absenceRequestForm;

    protected ?string $includeBefore = 'flux::livewire.human-resources.absence-requests';

    public function canChooseEmployee(): bool
    {
        return true;
    }

    #[Renderless]
    public function editAbsenceRequest($id): void
    {
        $this->redirectRoute(
            'human-resources.absence-requests.show',
            ['id' => $id],
            navigate: true
        );
    }

    protected function getRowActionEditButton(): DataTableButton
    {
        return DataTableButton::make()
            ->text(__('Edit'))
            ->icon('pencil')
            ->color('primary')
            ->wireClick('editAbsenceRequest(record.id)');
    }
}
