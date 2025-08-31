<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\WorkTimeModelList;
use FluxErp\Livewire\Forms\WorkTimeModelForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class WorkTimeModels extends WorkTimeModelList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public WorkTimeModelForm $workTimeModelForm;

    protected ?string $includeBefore = 'flux::livewire.settings.work-time-models';

    #[Renderless]
    public function editSchedule($id): void
    {
        $this->redirectRoute('settings.work-time-model', ['id' => $id], navigate: true);
    }

    protected function getRowActionEditButton(): DataTableButton
    {
        return DataTableButton::make()
            ->text(__('Edit'))
            ->icon('pencil')
            ->color('primary')
            ->wireClick('editSchedule(record.id)');
    }
}
