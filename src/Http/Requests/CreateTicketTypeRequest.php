<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use Illuminate\Database\Eloquent\Model;

class CreateTicketTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new TicketType())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:ticket_types,uuid',
                'name' => 'required|string',
                'model_type' => [
                    'string',
                    'nullable',
                    new ClassExists(instanceOf: Model::class),
                ],
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
