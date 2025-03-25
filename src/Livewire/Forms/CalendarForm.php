<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Calendar\CreateCalendar;
use FluxErp\Actions\Calendar\DeleteCalendar;
use FluxErp\Actions\Calendar\UpdateCalendar;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

class CalendarForm extends FluxForm
{
    public ?string $color = null;

    public ?array $custom_properties = null;

    public ?string $description = null;

    public bool $has_notifications = true;

    public bool $has_repeatable_events = true;

    #[Locked]
    public int|string|null $id = null;

    #[Locked]
    public bool $is_editable = true;

    public bool $is_public = false;

    public ?string $model_type = null;

    public ?string $name = null;

    public string|int|null $parent_id = null;

    public ?int $user_id = null;

    public function fill($values): void
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $values[Str::snake($key)] = $value;
            }
        }

        parent::fill($values);

        $this->user_id ??= auth()->id();
        $this->color ??= faker()->hexColor();
    }

    public function fromCalendarObject(?array $calendar): void
    {
        if (! $calendar) {
            $this->reset();

            return;
        }

        $mappedArray = [];

        foreach ($calendar as $key => $value) {
            $mappedArray[Str::snake($key)] = $value;
        }

        $this->fill($mappedArray);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateCalendar::class,
            'update' => UpdateCalendar::class,
            'delete' => DeleteCalendar::class,
        ];
    }
}
