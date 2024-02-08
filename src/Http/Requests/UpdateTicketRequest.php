<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateTicketRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new Ticket())->hasAdditionalColumnsValidationRules(),
            [
                'id' => [
                    'required',
                    'integer',
                    new ModelExists(Ticket::class),
                ],
                'authenticatable_type' => [
                    'required_with:authenticatable_id',
                    'string',
                    new ClassExists(instanceOf: AuthUser::class),
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
                'users' => 'array|nullable',
                'users.*' => [
                    'integer',
                    new ModelExists(User::class),
                ],
            ],
        );
    }
}
