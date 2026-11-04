<?php

namespace FluxErp\Traits\Action;

use FluxErp\Models\CalendarEvent;

trait ChecksCalendarEventAccess
{
    use ChecksCalendarAccess;

    protected function calendarIds(): array
    {
        return array_filter([
            $this->getData('calendar_id'),
            $this->getData('id')
                ? resolve_static(CalendarEvent::class, 'query')
                    ->whereKey($this->getData('id'))
                    ->value('calendar_id')
                : null,
        ]);
    }
}
