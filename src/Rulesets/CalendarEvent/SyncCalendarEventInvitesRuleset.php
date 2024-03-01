<?php

namespace FluxErp\Rulesets\CalendarEvent;

use FluxErp\Models\CalendarEvent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SyncCalendarEventInvitesRuleset extends FluxRuleset
{
    protected static ?string $model = CalendarEvent::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CalendarEvent::class),
            ],
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
