<?php

namespace FluxErp\Traits\Model\Calendar;

use FluxErp\Livewire\Features\Calendar\Calendar;
use FluxErp\Models\CalendarUserSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCalendarUserSettings
{
    public function calendarUserSettings(): MorphMany
    {
        return $this->morphMany(CalendarUserSetting::class, 'authenticatable');
    }

    public function getCalendarSettings(string|Calendar $calendar): Collection
    {
        return $this->calendarUserSettings()
            ->where('cache_key', is_string($calendar) ? $calendar : $calendar->getCacheKey())
            ->where('component', is_string($calendar) ? $calendar : get_class($calendar))
            ->get();
    }
}
