<?php

namespace FluxErp\Actions\CalendarEvent;

use Carbon\Exceptions\InvalidFormatException;
use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\CreateCalendarEventRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateCalendarEvent extends FluxAction
{
    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCalendarEventRuleset::class;
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

        if ($this->getData('is_all_day')) {
            if ($this->getData('start')) {
                try {
                    $this->data['start'] = Carbon::parse($this->getData('start'))
                        ->startOfDay()
                        ->toDateString();
                } catch (InvalidFormatException) {
                    //
                }
            }

            if ($this->getData('end')) {
                try {
                    $this->data['end'] = Carbon::parse($this->getData('end'))
                        ->startOfDay()
                        ->toDateString();
                } catch (InvalidFormatException) {
                    //
                }
            }
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $calendarModelType = resolve_static(Calendar::class, 'query')
            ->whereKey($this->data['calendar_id'])
            ->value('model_type');

        if (! is_null($calendarModelType)
            && data_get($this->data, 'model_type') !== $calendarModelType
        ) {
            throw ValidationException::withMessages([
                'model_type' => ['Model type must match the selected calendar\'s model type'],
            ])->errorBag('createCalendarEvent');
        }
    }
}
