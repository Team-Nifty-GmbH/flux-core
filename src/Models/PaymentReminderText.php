<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminderText extends FluxModel
{
    use HasAttributeTranslations, HasPackageFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'mail_to' => 'array',
            'mail_cc' => 'array',
        ];
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    protected function translatableAttributes(): array
    {
        return [
            'reminder_subject',
            'reminder_body',
        ];
    }
}
