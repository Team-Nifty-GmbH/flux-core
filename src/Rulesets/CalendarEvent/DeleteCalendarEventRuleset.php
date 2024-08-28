<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\CalendarEvent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCalendarEventRuleset extends FluxRuleset
{
    protected static ?string $model = CalendarEvent::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CalendarEvent::class]),
            ],
            'confirm_option' => 'required|string|in:this,future,all',
        ];
    }
}
