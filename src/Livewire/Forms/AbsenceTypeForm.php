<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\AbsenceType\DeleteAbsenceType;
use FluxErp\Actions\AbsenceType\UpdateAbsenceType;
use FluxErp\Models\AbsenceType;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class AbsenceTypeForm extends FluxForm
{
    use SupportsAutoRender;

    public ?array $absence_policies = null;

    public bool $affects_overtime = false;

    public bool $affects_sick_leave = false;

    public bool $affects_vacation = false;

    public ?string $code = null;

    public ?string $color = '#000000';

    public string $employee_can_create_enum = 'yes';

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    public ?float $percentage_deduction = 100.00;

    public function fill($values): void
    {
        if ($values instanceof AbsenceType) {
            $values->loadMissing('absencePolicies:id');
            $values = array_merge(
                $values->toArray(),
                [
                    'absence_policies' => $values->absencePolicies->pluck('id')->toArray(),
                ]
            );
        }

        parent::fill($values);

        $this->percentage_deduction = ! is_null($this->percentage_deduction)
            ? bcmul($this->percentage_deduction, 100)
            : null;
    }

    public function toActionData(): array
    {
        $data = parent::toActionData();
        $data['percentage_deduction'] = ! is_null($this->percentage_deduction)
            ? bcdiv($this->percentage_deduction, 100)
            : null;

        return $data;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateAbsenceType::class,
            'update' => UpdateAbsenceType::class,
            'delete' => DeleteAbsenceType::class,
        ];
    }
}
