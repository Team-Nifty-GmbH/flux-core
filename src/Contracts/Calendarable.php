<?php

namespace FluxErp\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface Calendarable
{
    public static function fromCalendarEvent(array $event): Model;

    public static function toCalendar(): array;

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void;

    public function toCalendarEvent(?array $info = null): array;
}
