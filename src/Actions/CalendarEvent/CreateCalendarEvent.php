<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;

class CreateCalendarEvent extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCalendarEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): CalendarEvent
    {
        $calendarEvent = new CalendarEvent($this->data);
        $calendarEvent->save();

        SyncCalendarEventInvites::make(array_merge($this->data, ['id' => $calendarEvent->id]))->execute();

        return $calendarEvent;
    }
}
