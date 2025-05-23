<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\UpdateCalendarRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateCalendar extends FluxAction
{
    public static function models(): array
    {
        return [Calendar::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCalendarRuleset::class;
    }

    public function performAction(): Model
    {
        $calendar = resolve_static(Calendar::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($calendar->is_group) {
            $this->data['custom_properties'] = null;
            $this->data['has_repeatable_events'] = false;
        }

        $calendar->fill($this->data);
        $calendar->save();

        return $calendar->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (
            data_get($this->data, 'parent_id')
            && Helper::checkCycle(Calendar::class, $this->data['id'], $this->data['parent_id'])
        ) {
            throw ValidationException::withMessages([
                'parent_id' => ['Cycle detected'],
            ])->errorBag('updateCalendar');
        }
    }
}
