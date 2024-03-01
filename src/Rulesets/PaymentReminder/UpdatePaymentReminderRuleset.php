<?php

namespace FluxErp\Rulesets\PaymentReminder;

use FluxErp\Models\Media;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePaymentReminderRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentReminder::class;

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
