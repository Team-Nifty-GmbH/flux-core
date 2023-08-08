<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateContactRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
            'client_id' => [
                'integer',
                (new ExistsWithIgnore('clients', 'id'))->whereNull('deleted_at'),
            ],
            'payment_type_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('payment_types', 'id'))->whereNull('deleted_at'),
            ],
            'price_list_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('price_lists', 'id'))->whereNull('deleted_at'),
            ],
            'customer_number' => 'sometimes|string',
            'creditor_number' => 'string|nullable',
            'debitor_number' => 'string|nullable',
            'payment_target_days' => 'sometimes|integer|nullable',
            'payment_reminder_days_1' => 'sometimes|integer|nullable',
            'payment_reminder_days_2' => 'sometimes|integer|nullable',
            'payment_reminder_days_3' => 'sometimes|integer|nullable',
            'discount_days' => 'sometimes|integer|nullable',
            'discount_percent' => 'sometimes|numeric|nullable',
            'credit_line' => 'sometimes|numeric|nullable',
            'has_sensitive_reminder' => 'sometimes|boolean',
            'has_delivery_lock' => 'sometimes|boolean',

            'discount_groups' => 'array',
            'discount_groups.*' => 'integer|exists:discount_groups,id',
        ];
    }
}
