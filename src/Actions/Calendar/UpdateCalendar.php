<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\UpdateCalendarRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendar extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateCalendarRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): Model
    {
        $calendar = resolve_static(Calendar::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $calendar->fill($this->data);
        $calendar->save();

        return $calendar->withoutRelations()->fresh();
    }
}
