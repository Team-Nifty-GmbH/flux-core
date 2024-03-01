<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\Address;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class InvitedAddressRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'invited_addresses' => 'array',
            'invited_addresses.*.id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
            'invited_addresses.*.status' => 'string|nullable|in:accepted,declined,maybe',
        ];
    }
}
