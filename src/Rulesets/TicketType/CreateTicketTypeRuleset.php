<?php

namespace FluxErp\Rulesets\TicketType;

use FluxErp\Models\TicketType;
use FluxErp\Rulesets\FluxRuleset;

class CreateTicketTypeRuleset extends FluxRuleset
{
    protected static ?string $model = TicketType::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:ticket_types,uuid',
            'name' => 'required|string|max:255',
        ];
    }
}
