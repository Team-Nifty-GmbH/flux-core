<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\RecordOriginList;
use FluxErp\Livewire\Forms\RecordOriginForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\HasRecordOrigin;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class RecordOrigins extends RecordOriginList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public RecordOriginForm $recordOriginForm;

    protected ?string $includeBefore = 'flux::livewire.settings.record-origins';

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'originTypes' => get_models_with_trait(HasRecordOrigin::class),
            ]
        );
    }

    protected function modalName(): string
    {
        return 'edit-record-origin-modal';
    }
}
