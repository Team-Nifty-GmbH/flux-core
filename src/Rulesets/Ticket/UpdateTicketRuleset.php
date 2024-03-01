<?php

namespace FluxErp\Rulesets\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Contracts\Auth\Authenticatable;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateTicketRuleset extends FluxRuleset
{
    protected static ?string $model = Ticket::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Ticket::class),
            ],
            'authenticatable_type' => [
                'required_with:authenticatable_id',
                'string',
                new MorphClassExists(implements: Authenticatable::class),
            ],
            'authenticatable_id' => [
                'required_with:authenticatable_type',
                'integer',
                new MorphExists('authenticatable_type'),
            ],
            'ticket_type_id' => [
                'integer',
                'nullable',
                new ModelExists(TicketType::class),
            ],
            'title' => 'sometimes|required|string',
            'description' => 'string|nullable',
            'state' => [
                'string',
                ValidStateRule::make(TicketState::class),
            ],
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(UserRuleset::class, 'getRules')
        );
    }
}
