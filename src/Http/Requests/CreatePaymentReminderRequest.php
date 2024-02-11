<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;

class CreatePaymentReminderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:payment_reminders,uuid',
            'order_id' => [
                'required',
                'integer',
                new ModelExists(Order::class),
            ],
            'media_id' => [
                'nullable',
                'integer',
                new ModelExists(Media::class),
            ],
            'reminder_level' => 'nullable|integer|min:1',
        ];
    }
}
