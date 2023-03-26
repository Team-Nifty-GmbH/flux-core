<?php

namespace FluxErp\Http\Requests;

class UpdateTicketTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:ticket_types,id,deleted_at,NULL',
            'name' => 'required|string',
        ];
    }
}
