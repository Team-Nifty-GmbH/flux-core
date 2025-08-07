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
    
    #[Locked]
    public ?int $id = null;

    public ?int $effective_year = null;

    public ?int $cutoff_month = null;

    public ?int $cutoff_day = null;

    public ?int $max_carryover_days = null;

    public ?string $expiry_date = null;

    public bool $is_active = true;

    public ?int $client_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateVacationCarryoverRule::class,
            'update' => UpdateVacationCarryoverRule::class,
            'delete' => DeleteVacationCarryoverRule::class,
        ];
    }

    protected static function getModel(): string
    {
        return VacationCarryoverRule::class;
    }
}