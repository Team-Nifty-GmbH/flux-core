<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\VacationCarryoverRule\CreateVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\DeleteVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\UpdateVacationCarryoverRule;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class VacationCarryoverRuleForm extends FluxForm
{
    use SupportsAutoRender;

    public ?int $client_id = null;

    public ?int $expires_after_months = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?int $max_days = null;

    public ?string $name = null;

    protected static function getModel(): string
    {
        return VacationCarryoverRule::class;
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateVacationCarryoverRule::class,
            'update' => UpdateVacationCarryoverRule::class,
            'delete' => DeleteVacationCarryoverRule::class,
        ];
    }
}
