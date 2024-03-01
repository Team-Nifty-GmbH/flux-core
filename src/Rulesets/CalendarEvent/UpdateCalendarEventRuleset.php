<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasCalendarEvents;

class UpdateCalendarEventRuleset extends FluxRuleset
{
    protected static ?string $model = CalendarEvent::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CalendarEvent::class),
            ],
            'calendar_id' => [
                'required_without_all:model_type,model_id',
                'integer',
                new ModelExists(Calendar::class),
            ],
            'model_type' => [
                'string',
                'nullable',
                new MorphClassExists(HasCalendarEvents::class),
            ],
            'model_id' => [
                'integer',
                'nullable',
                new MorphExists(),
            ],
            'title' => 'sometimes|required|string',
            'description' => 'string|nullable',
            'start' => 'sometimes|required|date_format:Y-m-d H:i',
            'end' => 'sometimes|required|date_format:Y-m-d H:i|after_or_equal:starts_at',
            'is_all_day' => 'boolean',
            'extended_props' => 'array|nullable',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(InvitedAddressRuleset::class, 'getRules'),
            resolve_static(InvitedUserRuleset::class, 'getRules')
        );
    }
}
