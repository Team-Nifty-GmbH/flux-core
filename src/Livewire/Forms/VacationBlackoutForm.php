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

    public ?string $end_date = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    public array $role_ids = [];

    public ?string $start_date = null;

    public array $user_ids = [];

    protected static function getModel(): string
    {
        return VacationBlackout::class;
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
