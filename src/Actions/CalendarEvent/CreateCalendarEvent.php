<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;

class CreateCalendarEvent extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCalendarEventRequest())->rules();
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function execute(): CalendarEvent
    {
        $calendarEvent = new CalendarEvent($this->data);
        $calendarEvent->save();

        SyncCalendarEventInvites::make(array_merge($this->data, ['id' => $calendarEvent->id]))->execute();

        return $calendarEvent;
    }
}
