<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\EmployeeDepartment\CreateEmployeeDepartment;
use FluxErp\Actions\EmployeeDepartment\DeleteEmployeeDepartment;
use FluxErp\Actions\EmployeeDepartment\UpdateEmployeeDepartment;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmployeeDepartmentForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $code = null;

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?int $location_id = null;

    public ?int $manager_employee_id = null;

    public ?string $name = null;

    public ?int $parent_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateEmployeeDepartment::class,
            'update' => UpdateEmployeeDepartment::class,
            'delete' => DeleteEmployeeDepartment::class,
        ];
    }
}
