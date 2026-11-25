<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\DocumentType\CreatePayrollDocumentTypes;
use FluxErp\Livewire\DataTables\DocumentTypeList;
use FluxErp\Livewire\Forms\DocumentTypeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use FluxErp\Traits\Model\InteractsWithMedia;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class DocumentTypes extends DocumentTypeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public DocumentTypeForm $documentTypeForm;

    protected ?string $includeBefore = 'flux::livewire.settings.document-types';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Add payroll document types'))
                ->icon('document-plus')
                ->when(resolve_static(CreatePayrollDocumentTypes::class, 'canPerformAction', [false]))
                ->wireClick('createPayrollDocumentTypes()'),
        ];
    }

    public function createPayrollDocumentTypes(): void
    {
        try {
            $created = CreatePayrollDocumentTypes::make()
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->toast()
            ->success(__(':count document types created', ['count' => $created->count()]))
            ->send();

        $this->loadData();
    }

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
