<?php

namespace FluxErp\Rulesets\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTicketRuleset extends FluxRuleset
{
    protected static ?string $model = Ticket::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Ticket::class]),
            ],
        ];
    }
}
