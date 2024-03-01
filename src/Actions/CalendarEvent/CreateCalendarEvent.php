<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\CreateCalendarEventRuleset;

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
        $calendarEvent = app(CalendarEvent::class, ['attributes' => $this->data]);
        $calendarEvent->save();

        SyncCalendarEventInvites::make(array_merge($this->data, ['id' => $calendarEvent->id]))->execute();

        return $calendarEvent->fresh();
    }
}
