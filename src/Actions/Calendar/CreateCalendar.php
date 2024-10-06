<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\CreateCalendarRuleset;
use Illuminate\Support\Arr;

class CreateCalendar extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCalendarRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): Calendar
    {
        $userId = Arr::pull($this->data, 'user_id');

        $calendar = app(Calendar::class, ['attributes' => $this->data]);
        $calendar->save();

        if ($userId) {
            $calendar->users()->attach($userId);
        }

        return $calendar->fresh();
    }
}
