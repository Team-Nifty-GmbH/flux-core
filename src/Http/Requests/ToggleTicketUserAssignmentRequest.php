<?php

namespace FluxErp\Http\Requests;

class ToggleTicketUserAssignmentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ticket_id' => 'required|integer|exists:tickets,id,deleted_at,NULL',
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }
}
