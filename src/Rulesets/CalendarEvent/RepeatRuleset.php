<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Rulesets\FluxRuleset;

class RepeatRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'repeat' => 'array|nullable',
            'repeat.interval' => 'required_with:repeat|integer|min:1',
            'repeat.unit' => 'required_with:repeat|string|in:days,weeks,months,years',
            'repeat.weekdays' => [
                'exclude_unless:repeat.unit,weeks',
                'required_if:repeat.unit,weeks',
                'array',
            ],
            'repeat.weekdays.*' => 'required|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'repeat.monthly' => [
                'exclude_unless:repeat.unit,months',
                'required_if:repeat.unit,months',
                'in:day,first,second,third,fourth,last',
            ],
            'repeat_end' => 'exclude_if:repeat,null|date|nullable|after:start',
            'recurrences' => 'exclude_if:repeat,null|integer|nullable|min:1',
        ];
    }
}
