<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Actions\CalendarEvent\DeleteCalendarEvent;
use FluxErp\Actions\CalendarEvent\UpdateCalendarEvent;
use FluxErp\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class CalendarEventForm extends FluxForm
{
    #[Locked]
    public int|string|null $calendar_id = null;

    #[Locked]
    public ?string $calendar_type = null;

    public ?string $confirm_option = null;

    public ?string $description = null;

    public ?string $end = null;

    public ?array $extended_props = null;

    public bool $has_taken_place = false;

    #[Locked]
    public ?int $id = null;

    public ?int $interval = null;

    public array $invited = [];

    public bool $is_all_day = false;

    public ?int $model_id = null;

    public ?string $model_type = null;

    public ?string $original_start = null;

    public ?int $recurrences = null;

    public ?array $repeat = null;

    public ?string $repeat_end = null;

    public ?string $start = null;

    public ?string $title = null;

    public function fill($values): void
    {
        if ($values instanceof Model) {
            $values = $values->toArray();
        }

        if (is_string(data_get($values, 'repeat'))) {
            $values['repeat'] = Helper::parseRepeatStringToArray(data_get($values, 'repeat'));
        }

        parent::fill($values);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateCalendarEvent::class,
            'update' => UpdateCalendarEvent::class,
            'delete' => DeleteCalendarEvent::class,
        ];
    }
}
