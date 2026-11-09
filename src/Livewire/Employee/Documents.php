<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\DocumentList;
use FluxErp\Models\Employee;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class Documents extends DocumentList
{
    #[Locked, Modelable]
    public ?int $employeeId = null;

    public function mount(): void
    {
        resolve_static(TabButton::class, 'make', ['component' => 'employee.documents'])
            ->userHasTabPermission();

        $this->modelType = morph_alias(Employee::class);
        $this->modelId = resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->firstOrFail()
            ->getKey();

        parent::mount();
    }
}
