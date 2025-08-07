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
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $color = '#000000';

    public bool $is_active = true;

    public bool $can_select_substitute = false;

    public bool $must_select_substitute = false;

    public bool $requires_proof = false;

    public bool $requires_reason = false;

    public string $employee_can_create = 'yes';

    public bool $counts_as_work_day = true;

    public bool $counts_as_target_hours = true;

    public bool $requires_work_day = false;

    public bool $is_vacation = false;

    public ?int $client_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateAbsenceType::class,
            'update' => UpdateAbsenceType::class,
            'delete' => DeleteAbsenceType::class,
        ];
    }

    protected static function getModel(): string
    {
        return AbsenceType::class;
    }
}