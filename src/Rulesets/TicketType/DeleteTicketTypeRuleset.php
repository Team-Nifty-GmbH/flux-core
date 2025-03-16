<?php

namespace FluxErp\Rulesets\TicketType;

use FluxErp\Models\TicketType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTicketTypeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = TicketType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => TicketType::class]),
            ],
        ];
    }
}
