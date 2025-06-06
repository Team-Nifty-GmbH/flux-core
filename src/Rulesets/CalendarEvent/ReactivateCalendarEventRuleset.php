<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\CalendarEvent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ReactivateCalendarEventRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = CalendarEvent::class;

    public static function getRules(): array
    {
        return array_merge(
            resolve_static(UpdateCalendarEventRuleset::class, 'getRules'),
            parent::getRules()
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CalendarEvent::class]),
            ],
            'confirm_option' => 'required|string|in:this,all',
            'original_start' => 'required_if:confirm_option,this|date|nullable',
            'repeat' => 'exclude',
            'repeat_end' => 'exclude',
            'recurrences' => 'exclude',
            'invited_addresses' => 'exclude',
            'invited' => 'exclude',
        ];
    }
}
