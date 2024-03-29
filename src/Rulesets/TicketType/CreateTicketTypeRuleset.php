<?php

namespace FluxErp\Rulesets\TicketType;

use FluxErp\Models\TicketType;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateTicketTypeRuleset extends FluxRuleset
{
    protected static ?string $model = TicketType::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:ticket_types,uuid',
            'name' => 'required|string',
            'model_type' => [
                'string',
                'nullable',
                new MorphClassExists(),
            ],
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
