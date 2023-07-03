<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Ticket;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\MorphExists;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Foundation\Auth\User;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateTicketRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Ticket())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:tickets,id,deleted_at,NULL',
                'authenticatable_type' => [
                    'required_with:authenticatable_id',
                    'string',
                    new ClassExists(instanceOf: User::class),
                ],
                'authenticatable_id' => [
                    'required_with:authenticatable_type',
                    'integer',
                    new MorphExists('authenticatable_type'),
                ],
                'ticket_type_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('ticket_types', 'id'))->whereNull('deleted_at'),
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
                    'exists:users,id,deleted_at,NULL',
                ],
            ],
        );
    }
}
