<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\VacationBlackout\CreateVacationBlackout;
use FluxErp\Actions\VacationBlackout\DeleteVacationBlackout;
use FluxErp\Actions\VacationBlackout\UpdateVacationBlackout;
use FluxErp\Models\VacationBlackout;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class VacationBlackoutForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $description = null;

    public array $employeeDepartments = [];

    public array $employees = [];

    public ?string $end_date = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public array $locations = [];

    public ?string $name = null;

    public ?string $start_date = null;

    public function fill($values): void
    {
        if ($values instanceof VacationBlackout) {
            $values->loadMissing([
                'employeeDepartments:id',
                'employees:id',
                'locations:id',
            ]);

            $valuesArray = $values->toArray();
            $valuesArray['employee_departments'] = $values->employeeDepartments->pluck('id')->toArray();
            $valuesArray['employees'] = $values->employees->pluck('id')->toArray();
            $valuesArray['locations'] = $values->locations->pluck('id')->toArray();
            $values = $valuesArray;
        }

        parent::fill($values);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateVacationBlackout::class,
            'update' => UpdateVacationBlackout::class,
            'delete' => DeleteVacationBlackout::class,
        ];
    }
}
