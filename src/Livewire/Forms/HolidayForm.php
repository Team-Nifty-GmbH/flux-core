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

    public ?string $date = null;

    public ?int $day = null;

    public ?string $effective_from = null;

    public ?string $effective_until = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_half_day = false;

    public bool $is_recurring = false;

    public ?int $location_id = null;

    public ?int $month = null;

    public ?string $name = null;

    protected static function getModel(): string
    {
        return Holiday::class;
    }

    public function updatedDate(): void
    {
        if ($this->date) {
            $dateObj = \Carbon\Carbon::parse($this->date);
            $this->month = $dateObj->month;
            $this->day = $dateObj->day;
            if (! $this->effective_from) {
                $this->effective_from = $this->date;
            }
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateHoliday::class,
            'update' => UpdateHoliday::class,
            'delete' => DeleteHoliday::class,
        ];
    }
}
