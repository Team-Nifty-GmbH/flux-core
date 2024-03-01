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

class CreateTicketRuleset extends FluxRuleset
{
    protected static ?string $model = Ticket::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:tickets,uuid',
            'authenticatable_type' => [
                'required',
                'string',
                new MorphClassExists(implements: Authenticatable::class),
            ],
            'authenticatable_id' => [
                'required',
                'integer',
                new MorphExists('authenticatable_type'),
            ],
            'model_type' => [
                'required_with:model_id',
                'string',
                new MorphClassExists(),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                new MorphExists(),
            ],
            'ticket_type_id' => [
                'integer',
                'nullable',
                new ModelExists(TicketType::class),
            ],
            'title' => 'required|string',
            'description' => 'required|string|min:12',
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
