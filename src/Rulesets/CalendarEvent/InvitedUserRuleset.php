<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class InvitedUserRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'invited_users' => 'array',
            'invited_users.*.id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'invited_users.*.status' => 'string|nullable|in:accepted,declined,maybe',
        ];
    }
}
