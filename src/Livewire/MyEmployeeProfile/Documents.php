<?php

namespace FluxErp\Livewire\MyEmployeeProfile;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\DocumentList;
use FluxErp\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Documents extends DocumentList
{
    #[Locked, Modelable]
    public ?int $employeeId = null;

    public function mount(): void
    {
        resolve_static(TabButton::class, 'make', ['component' => 'my-employee-profile.documents'])
            ->userHasTabPermission();

        $this->employeeId = auth()->user()?->employee?->getKey();
        abort_unless($this->employeeId, 404);

        $this->modelType = morph_alias(Employee::class);
        $this->modelId = $this->employeeId;

        parent::mount();
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Upload'))
                ->icon('arrow-up-tray')
                ->color('indigo')
                ->wireClick('addDocument()'),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Download'))
                ->icon('arrow-down-tray')
                ->wireClick('downloadDocument(record.id)'),
        ];
    }

    public function saveUpload(): bool
    {
        $this->documentForm->setCheckPermission(false);

        return parent::saveUpload();
    }

    protected function documentTypeQuery(): Builder
    {
        return parent::documentTypeQuery()->where('is_owner_uploadable', true);
    }
}
