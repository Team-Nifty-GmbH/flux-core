<?php

namespace FluxErp\Http\Requests;

class UpdatePaymentReminderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:payment_reminders,id,deleted_at,NULL',
            'media_id' => 'required|integer|exists:media,id',
        ];
    }
}
