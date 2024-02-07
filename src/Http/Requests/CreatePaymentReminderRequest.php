<?php

namespace FluxErp\Http\Requests;

class CreatePaymentReminderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:payment_reminders,uuid',
            'order_id' => 'required|integer|exists:orders,id,deleted_at,NULL',
            'media_id' => 'nullable|integer|exists:media,id',
            'reminder_level' => 'nullable|integer|min:1',
        ];
    }
}
