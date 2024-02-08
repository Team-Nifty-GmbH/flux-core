<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Media;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rules\ModelExists;

class UpdatePaymentReminderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PaymentReminder::class),
            ],
            'media_id' => [
                'required',
                'integer',
                new ModelExists(Media::class),
            ],
        ];
    }
}
