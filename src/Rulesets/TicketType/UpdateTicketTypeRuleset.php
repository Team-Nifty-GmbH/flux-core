<?php

namespace FluxErp\Rulesets\TicketType;

use FluxErp\Models\TicketType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateTicketTypeRuleset extends FluxRuleset
{
    protected static ?string $model = TicketType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(TicketType::class),
            ],
            'name' => 'required|string',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(RoleRuleset::class, 'getRules')
        );
    }
}
