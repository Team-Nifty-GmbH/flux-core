<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\CreateCalendarRuleset;
use Illuminate\Support\Arr;

class CreateCalendar extends FluxAction
{
    public static function models(): array
    {
        return [Calendar::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCalendarRuleset::class;
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
