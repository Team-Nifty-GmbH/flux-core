<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCalendarEventRuleset extends FluxRuleset
{
    protected static ?string $model = CalendarEvent::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CalendarEvent::class]),
            ],
            'calendar_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Calendar::class]),
            ],
            'title' => 'sometimes|required|string',
            'description' => 'string|nullable',
            'start' => 'required_if:confirm_option,this|required_if:confirm_option,future|date',
            'end' => 'required_if:confirm_option,this|required_if:confirm_option,future|date|after_or_equal:start',
            'is_all_day' => 'boolean',
            'extended_props' => 'array|nullable',
            'confirm_option' => 'required|string|in:this,future,all',
            'original_start' => 'required_if:confirm_option,this|required_if:confirm_option,future|date',
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
