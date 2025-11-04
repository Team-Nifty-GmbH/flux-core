<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\VacationCarryoverRule\CreateVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\DeleteVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\UpdateVacationCarryoverRule;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class VacationCarryOverRuleForm extends FluxForm
{
    use SupportsAutoRender;

    public ?int $expires_at_day = null;

    public ?int $expires_at_month = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_default = false;

    public ?int $max_days = null;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateVacationCarryoverRule::class,
            'update' => UpdateVacationCarryoverRule::class,
            'delete' => DeleteVacationCarryoverRule::class,
        ];
    }
}
