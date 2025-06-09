<?php

namespace FluxErp\Rulesets\TicketType;

use FluxErp\Models\TicketType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateTicketTypeRuleset extends FluxRuleset
{
    protected static ?string $model = TicketType::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(RoleRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => TicketType::class]),
            ],
            'name' => 'required|string|max:255',
        ];
    }
}
