<?php

namespace FluxErp\Rulesets\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ToggleTicketUserRuleset extends FluxRuleset
{
    protected static ?string $model = Ticket::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'ticket_id' => [
                'required',
                'integer',
                new ModelExists(Ticket::class),
            ],
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
        ];
    }
}
