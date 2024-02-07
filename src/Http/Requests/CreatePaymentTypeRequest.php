<?php

namespace FluxErp\Http\Requests;

class CreatePaymentTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:payment_types,uuid',
            'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
            'name' => 'required|string',
            'description' => 'string|nullable',
            'payment_reminder_days_1' => 'integer|nullable',
            'payment_reminder_days_2' => 'integer|nullable',
            'payment_reminder_days_3' => 'integer|nullable',
            'payment_target' => 'integer|nullable',
            'payment_discount_target' => 'integer|nullable',
            'payment_discount_percentage' => 'integer|nullable',
            'payment_reminder_text' => 'string|nullable',
            'payment_reminder_email_text' => 'string|nullable',
            'is_active' => 'boolean',
            'is_direct_debit' => 'boolean',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',
            'requires_manual_transfer' => 'boolean',
        ];
    }
}
