<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\WorkTimeModelList;
use FluxErp\Livewire\Forms\WorkTimeModelForm;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Livewire\Attributes\Renderless;

class WorkTimeModels extends WorkTimeModelList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public WorkTimeModelForm $workTimeModelForm;

    protected ?string $includeBefore = 'flux::livewire.settings.work-time-models';


    #[Renderless]
    public function edit(string|int|null $id = null): void
    {
        $this->workTimeModelForm->reset();

        if ($id) {
            $model = WorkTimeModel::with('schedules')
                ->whereKey($id)
                ->firstOrFail();
            $this->workTimeModelForm->fill($model);
        } else {
            $this->workTimeModelForm->initializeDefaultSchedules();
        }

        $modalName = $this->modalName();
        $this->js(<<<JS
            \$modalOpen('$modalName');
        JS);
    }
}