<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class InvitedRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'invited' => 'array',
            'invited.*.id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'invited.*.status' => 'string|nullable|in:accepted,declined,maybe',
        ];
    }
}
