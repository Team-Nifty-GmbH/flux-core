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
    
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $description = null;

    public bool $is_active = true;

    public array $role_ids = [];

    public array $user_ids = [];

    public ?int $client_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateVacationBlackout::class,
            'update' => UpdateVacationBlackout::class,
            'delete' => DeleteVacationBlackout::class,
        ];
    }

    protected static function getModel(): string
    {
        return VacationBlackout::class;
    }
}