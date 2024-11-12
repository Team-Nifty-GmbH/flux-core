<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\CreateCalendarEventRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateCalendarEvent extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateCalendarEventRuleset::class;
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

    protected function validateData(): void
    {
        parent::validateData();

        if (data_get($this->data, 'model_type') !== resolve_static(Calendar::class, 'query')
            ->whereKey($this->data['calendar_id'])
            ->value('model_type')
        ) {
            throw ValidationException::withMessages([
                'model_type' => [__('Model type must match the selected calendar\'s model type')],
            ])->errorBag('createCalendarEvent');
        }
    }
}
