<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCalendarEventRuleset extends FluxRuleset
{
    protected static ?string $model = CalendarEvent::class;

    public function rules(): array
    {
        return [
            'calendar_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Calendar::class]),
            ],
            'title' => 'required|string',
            'description' => 'string|nullable',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'is_all_day' => 'boolean',
            'extended_props' => 'array|nullable',
            'excluded' => 'array|nullable',
            'excluded.*' => 'date',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(RepeatRuleset::class, 'getRules'),
            resolve_static(InvitedAddressRuleset::class, 'getRules'),
            resolve_static(InvitedRuleset::class, 'getRules')
        );
    }
}
