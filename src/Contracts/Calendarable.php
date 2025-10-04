<?php

namespace FluxErp\Contracts;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use Illuminate\Database\Eloquent\Builder;

interface Calendarable
{
    public static function fromCalendarEvent(array $event, string $action): FluxAction;

    public static function toCalendar(): array;

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string $start,
        Carbon|string $end,
        ?array $info = null
    ): void;

    public function toCalendarEvent(?array $info = null): array;
}
