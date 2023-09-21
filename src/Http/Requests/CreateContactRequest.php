<?php

namespace FluxErp\Http\Requests;

class CreateContactRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:contacts,uuid',
            'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
            'payment_type_id' => 'sometimes|integer|nullable|exists:payment_types,id,deleted_at,NULL',
            'price_list_id' => 'sometimes|integer|nullable|exists:price_lists,id,deleted_at,NULL',
            'customer_number' => 'sometimes|required|string|unique:contacts,customer_number',
            'creditor_number' => 'string|nullable|unique:contacts,creditor_number',
            'debtor_number' => 'string|nullable|unique:contacts,debtor_number',
            'payment_target_days' => 'sometimes|integer|min:1|nullable',
            'payment_reminder_days_1' => 'sometimes|integer|min:1|nullable',
            'payment_reminder_days_2' => 'sometimes|integer|min:1|nullable',
            'payment_reminder_days_3' => 'sometimes|integer|min:1|nullable',
            'discount_days' => 'sometimes|integer|min:1|nullable',
            'discount_percent' => 'sometimes|numeric|min:0|max:100|nullable',
            'credit_line' => 'sometimes|numeric|min:0|nullable',
            'has_sensitive_reminder' => 'sometimes|boolean',
            'has_delivery_lock' => 'sometimes|boolean',

            'discount_groups' => 'array',
            'discount_groups.*' => 'integer|exists:discount_groups,id',
        ];
    }
}
