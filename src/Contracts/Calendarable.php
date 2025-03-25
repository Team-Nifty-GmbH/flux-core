<?php

namespace FluxErp\Contracts;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use Illuminate\Database\Eloquent\Builder;

interface Calendarable
{
    public static function fromCalendarEvent(array $event): FluxAction;

    public static function toCalendar(): array;

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void;

    public function toCalendarEvent(?array $info = null): array;
}
