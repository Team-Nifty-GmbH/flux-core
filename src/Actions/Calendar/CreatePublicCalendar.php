<?php

namespace FluxErp\Actions\Calendar;

class CreatePublicCalendar extends CreateCalendar
{
    public static function name(): string
    {
        return 'calendar.create-public-calendar';
    }
}
