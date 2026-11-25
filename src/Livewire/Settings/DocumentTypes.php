<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\DocumentTypeList;
use FluxErp\Livewire\Forms\DocumentTypeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Model\InteractsWithMedia;

class DocumentTypes extends DocumentTypeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public DocumentTypeForm $documentTypeForm;

    protected ?string $includeBefore = 'flux::livewire.settings.document-types';

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'modelTypes' => get_models_with_trait(InteractsWithMedia::class),
            ]
        );
    }

    protected function modalName(): string
    {
        return 'edit-document-type-modal';
    }
}
