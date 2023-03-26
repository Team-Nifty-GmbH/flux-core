<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdatePaymentTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:payment_types,id,deleted_at,NULL',
            'client_id' => [
                'integer',
                (new ExistsWithIgnore('clients', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'string',
            'description' => 'string|nullable',
            'payment_reminder_days_1' => 'integer|nullable',
            'payment_reminder_days_2' => 'integer|nullable',
            'payment_reminder_days_3' => 'integer|nullable',
            'payment_target' => 'integer|nullable',
            'payment_discount_target' => 'integer|nullable',
            'payment_discount_percentage' => 'integer|nullable',
            'is_active' => 'boolean',
        ];
    }
}
