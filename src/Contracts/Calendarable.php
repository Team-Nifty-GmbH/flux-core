<?php

namespace FluxErp\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Calendarable
{
    public static function toCalendar(): array;

    public function toCalendarEvent(): array;

    public static function fromCalendarEvent(array $event): Model;
}
