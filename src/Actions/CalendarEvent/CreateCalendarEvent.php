<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\CreateCalendarEventRuleset;
use Illuminate\Support\Arr;

class CreateCalendarEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCalendarEventRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): CalendarEvent
    {
        $repeat = Arr::pull($this->data, 'repeat');

        if ($repeat) {
            $this->data['repeat'] = Helper::buildRepeatStringFromArray(
                array_merge($repeat, ['start' => $this->data['start']])
            );
        }

        $calendarEvent = app(CalendarEvent::class, ['attributes' => $this->data]);
        $calendarEvent->save();

        SyncCalendarEventInvites::make(array_merge($this->data, ['id' => $calendarEvent->id]))
            ->validate()
            ->execute();

        return $calendarEvent->fresh();
    }

    protected function prepareForValidation(): void
    {
        $repeat = data_get($this->data, 'repeat');

        if (is_string($repeat)) {
            $this->data['repeat'] = Helper::parseRepeatStringToArray($repeat);
        }
    }
}
