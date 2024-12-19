<?php

namespace FluxErp\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface Calendarable
{
    public static function toCalendar(): array;

    public function toCalendarEvent(): array;

    public function scopeInTimeframe(Builder $builder, string|Carbon|null $start, string|Carbon|null $end): void;

    public static function fromCalendarEvent(array $event): Model;
}
