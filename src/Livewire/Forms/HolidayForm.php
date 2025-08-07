<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Holiday\CreateHoliday;
use FluxErp\Actions\Holiday\DeleteHoliday;
use FluxErp\Actions\Holiday\UpdateHoliday;
use FluxErp\Models\Holiday;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class HolidayForm extends FluxForm
{
    use SupportsAutoRender;
    
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $date = null;

    public ?int $month = null;

    public ?int $day = null;

    public bool $is_recurring = false;

    public ?int $effective_from = null;

    public ?int $effective_until = null;

    public string $day_part = 'full';

    public bool $is_active = true;

    public ?int $location_id = null;

    public ?int $client_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateHoliday::class,
            'update' => UpdateHoliday::class,
            'delete' => DeleteHoliday::class,
        ];
    }

    protected static function getModel(): string
    {
        return Holiday::class;
    }
}