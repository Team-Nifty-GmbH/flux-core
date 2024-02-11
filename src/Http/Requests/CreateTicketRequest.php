<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateTicketRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new Ticket())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:tickets,uuid',
                'authenticatable_type' => [
                    'required',
                    'string',
                    new ClassExists(instanceOf: AuthUser::class),
                ],
                'authenticatable_id' => [
                    'required',
                    'integer',
                    new MorphExists('authenticatable_type'),
                ],
                'model_type' => [
                    'required_with:model_id',
                    'string',
                    new ClassExists(instanceOf: Model::class),
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
                'users' => 'array',
                'users.*' => [
                    'integer',
                    new ModelExists(User::class),
                ],
            ],
        );
    }
}
