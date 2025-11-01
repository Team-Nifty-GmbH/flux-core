<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Livewire\DataTables\AbsenceRequestList;
use FluxErp\Livewire\Forms\AbsenceRequestForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;

class AbsenceRequests extends AbsenceRequestList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::save as traitSave;
        DataTableHasFormEdit::edit as traitEdit;
    }

    protected static string $detailRouteName = 'human-resources.absence-requests.show';

    #[DataTableForm]
    public AbsenceRequestForm $absenceRequestForm;

    protected ?string $includeBefore = 'flux::livewire.human-resources.absence-requests';

    public function canChooseEmployee(): bool
    {
        return true;
    }

    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        if ($id) {
            $this->redirectRoute(
                name: static::$detailRouteName,
                parameters: ['id' => $id],
                navigate: true
            );

            return;
        }

        $this->traitEdit($id);
    }

    #[Renderless]
    public function save(): bool
    {
        $result = $this->traitSave();

        if ($result) {
            $this->redirectRoute(
                name: static::$detailRouteName,
                parameters: ['id' => $this->absenceRequestForm->id],
                navigate: true
            );
        }

        return $result;
    }
}
