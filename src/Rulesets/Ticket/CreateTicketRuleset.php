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

class CreateTicketRuleset extends FluxRuleset
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
            'uuid' => 'nullable|string|uuid|unique:tickets,uuid',
            'authenticatable_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['implements' => Authenticatable::class]),
            ],
            'authenticatable_id' => [
                'required',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'authenticatable_type']),
            ],
            'model_type' => [
                'required_with:model_id',
                'string',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
            'ticket_type_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => TicketType::class]),
            ],
            'title' => 'required|string',
            'description' => 'required|string|min:12',
            'state' => [
                'string',
                ValidStateRule::make(TicketState::class),
            ],
        ];
    }
}
