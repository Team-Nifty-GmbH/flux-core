<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Ticket;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\States\Ticket\TicketState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateTicketRequest extends BaseFormRequest
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
                'authenticatable_type' => [
                    'required',
                    'string',
                    new ClassExists(instanceOf: User::class),
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
                'ticket_type_id' => 'integer|nullable|exists:ticket_types,id,deleted_at,NULL',
                'title' => 'required|string',
                'description' => 'required|string|min:12',
                'state' => [
                    'string',
                    ValidStateRule::make(TicketState::class),
                ],
                'users' => 'array',
                'users.*' => [
                    'integer',
                    'exists:users,id,deleted_at,NULL',
                ],
            ],
        );
    }
}
