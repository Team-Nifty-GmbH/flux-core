<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\TicketType;

class UpdateTicketTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new TicketType())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:ticket_types,id,deleted_at,NULL',
                'name' => 'required|string',
            ],
        );
    }
}
