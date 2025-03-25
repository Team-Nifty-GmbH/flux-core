<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Model;

class CalendarEventForm extends FluxForm
{
    public int|string|null $calendar_id = null;

    public ?string $calendar_type = null;

    public string $confirm_option = 'all';

    public ?string $description = null;

    public string $edit_component = 'test';

    public ?string $end = null;

    public ?array $extended_props = null;

    public bool $has_repeats = false;

    public bool $has_taken_place = false;

    public string|int|null $id = null;

    public array $invited = [];

    public bool $is_all_day = false;

    public bool $is_editable = true;

    public bool $is_repeatable = false;

    public ?int $model_id = null;

    public ?string $model_type = null;

    public ?string $original_start = null;

    public ?int $recurrences = null;

    public ?array $repeat = [
        'interval' => 1,
        'unit' => 'days',
        'weekdays' => [],
        'monthly' => 'day',
        'repeat_end' => null,
        'recurrences' => null,
        'repeat_radio' => null,
    ];

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

        $this->end ??= $this->start;
    }

    public function fillFromJs(array $values): void
    {
        $values['is_all_day'] = data_get($values, 'allDay');

        $this->fill($values);
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $model = morphed_model(data_get($this->extended_props, 'calendar_type') ?? '')
            ?? CalendarEvent::class;

        return $model::fromCalendarEvent($this->toArray());
    }
}
