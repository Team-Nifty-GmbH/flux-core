<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use FluxErp\Rules\ModelExists;

class UpdateTicketTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new TicketType())->hasAdditionalColumnsValidationRules(),
            [
                'id' => [
                    'required',
                    'integer',
                    new ModelExists(TicketType::class),
                ],
                'name' => 'required|string',
                'roles' => 'array',
                'roles.*' => [
                    'required',
                    'integer',
                    new ModelExists(Role::class),
                ],
            ],
        );
    }
}
