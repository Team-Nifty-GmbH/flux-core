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

    public ?int $client_id = null;

    public ?string $description = null;

    public array $employee_department_ids = [];

    public array $employee_ids = [];

    public ?string $end_date = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public array $location_ids = [];

    public ?string $name = null;

    public ?string $start_date = null;

    public function fill($values): void
    {
        if ($values instanceof VacationBlackout) {
            $valuesArray = $values->toArray();
            $valuesArray['employee_department_ids'] = $values->employeeDepartments->pluck('id')->toArray();
            $valuesArray['employee_ids'] = $values->employees->pluck('id')->toArray();
            $valuesArray['location_ids'] = $values->locations->pluck('id')->toArray();
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
