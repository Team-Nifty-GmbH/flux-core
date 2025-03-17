<?php

namespace FluxErp\Rulesets\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Contracts\Auth\Authenticatable;

class UpdateTicketRuleset extends FluxRuleset
{
    protected static ?string $model = Ticket::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(UserRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Ticket::class]),
            ],
            'authenticatable_type' => [
                'required_with:authenticatable_id',
                'string',
                app(MorphClassExists::class, ['implements' => Authenticatable::class]),
            ],
            'authenticatable_id' => [
                'required_with:authenticatable_type',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'authenticatable_type']),
            ],
            'ticket_type_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => TicketType::class]),
            ],
            'title' => 'sometimes|required|string',
            'description' => 'string|nullable',
            'state' => [
                'string',
                ValidStateRule::make(TicketState::class),
            ],
        ];
    }
}
