<?php

namespace FluxErp\Actions\CalendarEvent;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\UpdateCalendarEventRuleset;
use Illuminate\Support\Arr;

class UpdateCalendarEvent extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateCalendarEventRuleset::class;
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): array
    {
        $confirmOption = Arr::pull($this->data, 'confirm_option');
        $repeat = Arr::pull($this->data, 'repeat');

        $calendarEvent = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($repeat) {
            $this->data['repeat'] = Helper::buildRepeatStringFromArray(
                array_merge($repeat, ['start' => $this->data['start'] ?? $calendarEvent->start->toDateTimeString()])
            );
        }

        // If existing event is not repeatable, confirmOption should be "all".
        switch ($confirmOption) {
            case 'this':
                $calendarEvent->fill([
                    'excluded' => array_merge(
                        $calendarEvent->excluded ?: [],
                        [Carbon::parse(data_get($this->data, 'original_start'))->toDateTimeString()]
                    ),
                ]);
                break;
            case 'future':
                $calendarEvent->fill([
                    'repeat_end' => Carbon::parse(data_get($this->data, 'original_start'))
                        ->subSecond()
                        ->toDateTimeString(),
                ]);
                break;
            default:
                $calendarEvent->fill($this->data);
                break;
        }

        $calendarEvent->save();

        $createdEvent = null;
        if (in_array($confirmOption, ['this', 'future'])) {
            $createdEvent = CreateCalendarEvent::make($this->data)
                ->validate()
                ->execute();
        } else {
            SyncCalendarEventInvites::make($this->data)
                ->validate()
                ->execute();
        }

        return [
            'created' => $createdEvent,
            'updated' => $calendarEvent->withoutRelations()->fresh(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $repeat = data_get($this->data, 'repeat');

        if (is_string($repeat)) {
            $this->data['repeat'] = Helper::parseRepeatStringToArray($repeat);
        }
    }
}
